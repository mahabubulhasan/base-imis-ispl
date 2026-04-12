<?php

namespace App\Http\Requests\Swm;

use App\Models\Swm\Worker;
use App\Services\Swm\VehicleService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
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
        }
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
                $orgId = $this->input('organization_id');
                $vehicleId = $this->vehicleId();

                $vehicleNumberUnique = Rule::unique('pgsql.swm.vehicles', 'vehicle_number')
                    ->where(function ($query) use ($orgId) {
                        return $query->where('organization_id', $orgId)->whereNull('deleted_at');
                    });
                if ($vehicleId) {
                    $vehicleNumberUnique->ignore($vehicleId);
                }

                return [
                    'organization_id' => [
                        'required',
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
                            $ok = Worker::query()
                                ->whereKey($value)
                                ->where('organization_id', $orgId)
                                ->where('work_type_id', $wtId)
                                ->whereNull('deleted_at')
                                ->exists();
                            if (! $ok) {
                                $fail(__('The selected driver is invalid for this organization.'));
                            }
                        },
                    ],
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
            'organization_id.required' => __('The organization is required.'),
            'vehicle_type_id.required' => __('The vehicle type is required.'),
            'vehicle_number.required' => __('The vehicle number is required.'),
            'driver_worker_id.required' => __('The driver is required.'),
            'dumping_place_kind.required' => __('The dumping place type is required.'),
        ];
    }
}
