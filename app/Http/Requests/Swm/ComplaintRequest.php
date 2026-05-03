<?php

namespace App\Http\Requests\Swm;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ComplaintRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function complaintId(): ?int
    {
        $complaint = $this->route('complaint');

        return is_object($complaint) ? (int) $complaint->id : ($complaint !== null ? (int) $complaint : null);
    }

    public function rules(): array
    {
        $id = $this->complaintId();

        if (in_array($this->method(), ['GET', 'DELETE'], true)) {
            return [];
        }

        return [
            'complaint_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pgsql.swm.complaints', 'complaint_id')->whereNull('deleted_at')->ignore($id),
            ],
            'date_time' => ['required', 'date'],
            'holding_number' => ['nullable', 'string', 'max:255'],
            'household_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:255'],
            'ward_no' => ['nullable', 'string', 'max:255'],
            'incident_date' => ['nullable', 'date'],
            'complaint_type' => [
                'required',
                'string',
                Rule::in(array_keys(config('swm_complaints.complaint_types', []))),
            ],
            'complaint_details' => ['required', 'string'],
            'duplicate_complaint' => ['nullable', 'boolean'],
            'duplicate_reference' => ['nullable', 'string', 'max:255'],
            'priority_level' => ['nullable', 'integer', 'between:1,5'],
            'assigned_to' => ['nullable', 'string', 'max:255'],
            'submitted_through' => [
                'required',
                'string',
                Rule::in(array_keys(config('swm_complaints.submitted_through', []))),
            ],
            'complaint_status' => [
                'required',
                'string',
                Rule::in(array_keys(config('swm_complaints.complaint_statuses', []))),
            ],
            'resolution_time_days' => ['nullable', 'integer', 'min:0'],
            'photo_attachment' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'complaint_id' => $this->filled('complaint_id') ? trim((string) $this->input('complaint_id')) : null,
            'holding_number' => $this->filled('holding_number') ? trim((string) $this->input('holding_number')) : null,
            'household_id' => $this->filled('household_id') ? trim((string) $this->input('household_id')) : null,
            'name' => trim((string) $this->input('name', '')),
            'contact_number' => trim((string) $this->input('contact_number', '')),
            'ward_no' => $this->filled('ward_no') ? trim((string) $this->input('ward_no')) : null,
            'duplicate_reference' => $this->filled('duplicate_reference') ? trim((string) $this->input('duplicate_reference')) : null,
            'assigned_to' => $this->filled('assigned_to') ? trim((string) $this->input('assigned_to')) : null,
            'resolution_time_days' => $this->filled('resolution_time_days') ? (int) $this->input('resolution_time_days') : null,
            'priority_level' => $this->filled('priority_level') ? (int) $this->input('priority_level') : null,
            'duplicate_complaint' => filter_var($this->input('duplicate_complaint', false), FILTER_VALIDATE_BOOLEAN),
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }
}
