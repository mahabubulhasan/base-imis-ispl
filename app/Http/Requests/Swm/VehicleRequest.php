<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Models\Swm\Worker;
use App\Services\Swm\VehicleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    use MapsValidationAttributes;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (Auth::user()?->swm_organization_id) {
            $this->merge([
                'organization_id' => Auth::user()->swm_organization_id,
            ]);
        } elseif ($this->has('organization_id') && $this->input('organization_id') === '') {
            $this->merge(['organization_id' => null]);
        }

        $serviceWards = $this->input('service_wards');
        if (is_string($serviceWards)) {
            $serviceWards = $serviceWards === '' ? null : array_filter(array_map('trim', explode(',', $serviceWards)));
        }
        if (is_array($serviceWards)) {
            $serviceWards = array_values(array_filter($serviceWards, function ($value) {
                return $value !== '' && $value !== null;
            }));
            $serviceWards = array_map('intval', $serviceWards);
            if (empty($serviceWards)) {
                $serviceWards = null;
            }
        }

        $merge = [
            'service_wards' => $serviceWards,
        ];

        if ($this->input('operational_type') !== 'other') {
            $merge['operational_type_other'] = null;
        }

        $this->merge($merge);
    }

    protected function vehicleId(): ?int
    {
        $v = $this->route('vehicle');

        return is_object($v) ? (int) $v->id : ($v !== null ? (int) $v : null);
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
                return [];
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $rawOrg = $this->input('organization_id');
                $orgId = $rawOrg === null || $rawOrg === '' ? null : (int) $rawOrg;
                $vehicleId = $this->vehicleId();

                $vehicleNumberUnique = Rule::unique('pgsql.swm.vehicles', 'vehicle_number')
                    ->where(function ($query) use ($orgId) {
                        if ($orgId === null) {
                            return $query->whereNull('organization_id')->whereNull('deleted_at');
                        }

                        return $query->where('organization_id', $orgId)->whereNull('deleted_at');
                    });
                if ($vehicleId) {
                    $vehicleNumberUnique->ignore($vehicleId);
                }

                $vehicleIdNoUnique = Rule::unique('pgsql.swm.vehicles', 'vehicle_id_no')
                    ->where(function ($query) use ($orgId) {
                        if ($orgId === null) {
                            return $query->whereNull('organization_id')->whereNull('deleted_at');
                        }

                        return $query
                            ->where('organization_id', $orgId)
                            ->whereNull('deleted_at');
                    });
                if ($vehicleId) {
                    $vehicleIdNoUnique->ignore($vehicleId);
                }

                $chassisNoUnique = Rule::unique('pgsql.swm.vehicles', 'chassis_no')
                    ->where(function ($query) use ($orgId) {
                        if ($orgId === null) {
                            return $query->whereNull('organization_id')->whereNull('deleted_at');
                        }

                        return $query
                            ->where('organization_id', $orgId)
                            ->whereNull('deleted_at');
                    });
                if ($vehicleId) {
                    $chassisNoUnique->ignore($vehicleId);
                }

                return [
                    'organization_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.organizations', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'vehicle_type_id' => [
                        'required',
                        'integer',
                        Rule::exists('pgsql.swm.vehicle_types', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'vehicle_number' => [
                        'required',
                        'string',
                        'max:255',
                        $vehicleNumberUnique,
                    ],
                    'capacity' => ['nullable', 'string', 'max:255'],
                    'vehicle_id_no' => [
                        'nullable',
                        'string',
                        'max:255',
                        $vehicleIdNoUnique,
                    ],
                    'driver_worker_id' => [
                        'required',
                        'integer',
                        function ($attribute, $value, $fail) use ($orgId) {
                            $wtId = VehicleService::driverWorkTypeId();
                            if (! $wtId) {
                                $fail(__('The Driver work type is not configured. Add a work type named :name.', [
                                    'name' => config('swm.driver_work_type_name', 'Driver'),
                                ]));

                                return;
                            }
                            $query = Worker::query()
                                ->whereKey($value)
                                ->where('work_type_id', $wtId)
                                ->whereNull('deleted_at');
                            if ($orgId !== null) {
                                $query->where('organization_id', $orgId);
                            }
                            if (! $query->exists()) {
                                $fail($orgId !== null
                                    ? __('The selected driver is invalid for this organization.')
                                    : __('The selected driver is invalid.'));
                            }
                        },
                    ],
                    'service_wards' => ['nullable', 'array'],
                    'service_wards.*' => [
                        'integer',
                        Rule::exists('pgsql.layer_info.wards', 'ward'),
                    ],
                    'fuel_type' => ['nullable', 'string', 'max:255'],
                    'operational_type' => ['nullable', 'string', Rule::in(['day', 'night', 'mobile', 'other'])],
                    'operational_type_other' => [
                        'nullable',
                        'string',
                        'max:255',
                        Rule::requiredIf(fn () => $this->input('operational_type') === 'other'),
                    ],
                    'engine_no' => ['nullable', 'string', 'max:255'],
                    'chassis_no' => [
                        'nullable',
                        'string',
                        'max:255',
                        $chassisNoUnique,
                    ],
                    'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
                    'last_maintenance_year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:2100'],
                    'remarks' => ['nullable', 'string', 'max:2000'],
                    'dumping_place_kind' => ['required', 'string', Rule::in(['sts', 'landfill', 'other'])],
                    'dumping_sts_id' => [
                        Rule::requiredIf(fn () => $this->input('dumping_place_kind') === 'sts'),
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.sts', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'dumping_landfill_id' => [
                        Rule::requiredIf(fn () => $this->input('dumping_place_kind') === 'landfill'),
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.swm.landfills', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'dumping_place_other' => [
                        Rule::requiredIf(fn () => $this->input('dumping_place_kind') === 'other'),
                        'nullable',
                        'string',
                        'max:2000',
                    ],
                ];
            default:
                return [];
        }
    }

    public function messages(): array
    {
        return [
            'service_wards.array' => __('Service wards must be a list.'),
            'service_wards.*.integer' => __('Each service ward must be a valid ward number.'),
            'service_wards.*.exists' => __('One or more selected service wards are invalid.'),
            'operational_type_other.required_if' => __('Please specify operational type when selecting Others (specify).'),
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(VehicleService::class)->validationAttributeLabels();
    }
}
