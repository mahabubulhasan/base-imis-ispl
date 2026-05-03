<?php

namespace App\Http\Requests\Swm;

use App\Models\BuildingInfo\Household;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillCollectionPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
                $methodKeys = array_keys(config('bill_collection.payment_methods', []));

                return [
                    'household_id' => [
                        'required',
                        'integer',
                        Rule::exists('pgsql.building_info.households', 'id')->where(function ($query) {
                            return $query->whereNull('deleted_at');
                        }),
                    ],
                    'holding_number' => ['required', 'string', 'max:255'],
                    'household_code' => ['required', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'min:0.01'],
                    'payment_for_month' => ['required', 'date'],
                    'payment_time' => ['nullable', 'date'],
                    'payment_method' => ['required', 'string', Rule::in($methodKeys)],
                    'received_by_user_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('pgsql.auth.users', 'id'),
                    ],
                    'receipt_copy' => [
                        'nullable',
                        'file',
                        'max:10240',
                        'mimes:jpg,jpeg,png,pdf',
                    ],
                    'receipt_no' => ['nullable', 'string', 'max:255'],
                ];
            default:
                return [];
        }
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('received_by_user_id') === '') {
            $this->merge(['received_by_user_id' => null]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $siteId = $this->input('household_id');
            if (! $siteId) {
                return;
            }
            $site = Household::query()
                ->whereKey($siteId)
                ->whereNull('deleted_at')
                ->first();
            if (! $site) {
                $validator->errors()->add('household_id', __('Invalid household.'));

                return;
            }
            $hn = (string) $this->input('holding_number', '');
            $cid = (string) $this->input('household_code', '');
            if (($site->holding_number ?? '') !== $hn || (string) $site->household_id !== $cid) {
                $validator->errors()->add('household_id', __('Holding number and household ID must match the selected household.'));
            }
        });
    }
}
