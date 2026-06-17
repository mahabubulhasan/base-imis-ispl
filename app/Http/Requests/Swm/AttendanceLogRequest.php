<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Models\Swm\AttendanceLog;
use App\Services\Swm\AttendanceLogService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AttendanceLogRequest extends FormRequest
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
        }

        foreach (['check_in_at', 'check_out_at'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }

        $status = $this->input('attendance_status');
        if ($status !== AttendanceLog::STATUS_PRESENT) {
            $this->merge([
                'check_in_at' => null,
                'check_out_at' => null,
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('attendance_status') !== AttendanceLog::STATUS_PRESENT) {
                return;
            }
            $in = $this->input('check_in_at');
            $out = $this->input('check_out_at');
            if ($in && $out && strtotime((string) $out) < strtotime((string) $in)) {
                $validator->errors()->add('check_out_at', __('Check-out must be on or after check-in.'));
            }
        });
    }

    public function rules(): array
    {
        $orgId = $this->input('organization_id');

        return [
            'organization_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.organizations', 'id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
            'worker_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.workers', 'id')->where(function ($query) use ($orgId) {
                    return $query
                        ->whereNull('deleted_at')
                        ->when($orgId, fn ($q) => $q->where('organization_id', (int) $orgId));
                }),
            ],
            'department' => ['nullable', 'string', 'max:255'],
            'entry_at' => ['required', 'date'],
            'attendance_status' => ['required', Rule::in([
                AttendanceLog::STATUS_PRESENT,
                AttendanceLog::STATUS_ABSENT,
                AttendanceLog::STATUS_ON_LEAVE,
            ])],
            'check_in_at' => [
                Rule::requiredIf(fn () => $this->input('attendance_status') === AttendanceLog::STATUS_PRESENT),
                'nullable',
                'date',
            ],
            'check_out_at' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(AttendanceLogService::class)->validationAttributeLabels();
    }
}
