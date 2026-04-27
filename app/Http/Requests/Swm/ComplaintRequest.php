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
                'required',
                'string',
                'max:255',
                Rule::unique('pgsql.swm.complaints', 'complaint_id')->whereNull('deleted_at')->ignore($id),
            ],
            'date_time' => ['required', 'date'],
            'holding_number' => ['nullable', 'string', 'max:255'],
            'household_id' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'contact_number' => ['required', 'string', 'max:255'],
            'complaint_type' => [
                'required',
                'string',
                Rule::in(array_keys(config('swm_complaints.complaint_types', []))),
            ],
            'complaint_details' => ['required', 'string'],
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
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'complaint_id' => trim((string) $this->input('complaint_id', '')),
            'holding_number' => $this->filled('holding_number') ? trim((string) $this->input('holding_number')) : null,
            'household_id' => $this->filled('household_id') ? trim((string) $this->input('household_id')) : null,
            'name' => trim((string) $this->input('name', '')),
            'contact_number' => trim((string) $this->input('contact_number', '')),
            'notes' => $this->filled('notes') ? trim((string) $this->input('notes')) : null,
        ]);
    }
}
