<?php

namespace App\Http\Requests\Swm;

use App\Http\Requests\Concerns\MapsValidationAttributes;
use App\Models\BuildingInfo\Household;
use App\Models\Swm\BillCollectionPayment;
use App\Services\Swm\BillCollectionPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillCollectionPaymentRequest extends FormRequest
{
    use MapsValidationAttributes;

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
                            $query->whereNull('deleted_at');
                            $payment = $this->route('payment');
                            $isSameHouseholdOnUpdate = $payment instanceof BillCollectionPayment
                                && (int) $this->input('household_id') === (int) $payment->household_id;
                            if (! $isSameHouseholdOnUpdate) {
                                $query->where('status', Household::STATUS_ACTIVE);
                            }
                        }),
                    ],
                    'holding_number' => ['required', 'string', 'max:255'],
                    'household_code' => ['required', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'min:0'],
                    'due_paid' => ['nullable', 'numeric', 'min:0'],
                    'payment_for_month' => ['required', 'date'],
                    'payment_time' => ['nullable', 'date'],
                    'payment_method' => ['nullable', 'string', Rule::in($methodKeys)],
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
        $this->merge([
            'payment_for_month' => now()->startOfMonth()->toDateString(),
        ]);
        if ($this->input('received_by_user_id') === '') {
            $this->merge(['received_by_user_id' => null]);
        }
        if ($this->input('payment_method') === '') {
            $this->merge(['payment_method' => null]);
        }
        if ($this->input('due_paid') === '' || $this->input('due_paid') === null) {
            $this->merge(['due_paid' => 0]);
        }
        if ($this->input('amount') === '' || $this->input('amount') === null) {
            $this->merge(['amount' => 0]);
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

            $amount = (float) $this->input('amount', 0);
            $duePaid = (float) $this->input('due_paid', 0);
            if (($amount + $duePaid) <= 0) {
                $validator->errors()->add(
                    'amount',
                    __('At least one of current month paid or previous due paid must be greater than 0.')
                );

                return;
            }
            $fixedCharge = $site->waste_charge !== null ? (float) $site->waste_charge : null;
            if ($fixedCharge !== null && $amount > $fixedCharge) {
                $validator->errors()->add(
                    'amount',
                    __('Current month paid cannot be greater than the waste collection fee (:fee).', [
                        'fee' => number_format($fixedCharge, 2, '.', ''),
                    ])
                );
            }

            $selectedMonthRaw = $this->input('payment_for_month');
            if ($fixedCharge !== null && $selectedMonthRaw) {
                try {
                    $selectedMonth = Carbon::parse((string) $selectedMonthRaw)->startOfMonth()->toDateString();
                    $existingAmountQuery = BillCollectionPayment::query()
                        ->whereNull('deleted_at')
                        ->where('household_id', (int) $siteId)
                        ->whereDate('payment_for_month', $selectedMonth);

                    $payment = $this->route('payment');
                    if ($payment instanceof BillCollectionPayment) {
                        $existingAmountQuery->where('id', '!=', $payment->id);
                    }

                    $existingAmountSum = (float) $existingAmountQuery->sum('amount');
                    $isCreate = ! ($payment instanceof BillCollectionPayment);
                    if ($isCreate && $existingAmountSum >= $fixedCharge && $duePaid <= 0) {
                        $validator->errors()->add(
                            'due_paid',
                            __('Previous due paid is required and must be greater than 0 when current month charge is already fully paid.')
                        );

                        return;
                    }
                    if ($existingAmountSum >= $fixedCharge && $amount > 0) {
                        $validator->errors()->add(
                            'amount',
                            __('The current month\'s waste collection fee has been paid. You may only pay previous dues.')
                        );

                        return;
                    }
                    if (($existingAmountSum + $amount) > $fixedCharge) {
                        $validator->errors()->add(
                            'amount',
                            __('Total current month paid for this household and month cannot exceed waste collection fee (:fee).', [
                                'fee' => number_format($fixedCharge, 2, '.', ''),
                            ])
                        );
                    }
                } catch (\Throwable) {
                    // payment_for_month format validation handles invalid dates.
                }
            }
        });
    }

    /** @return array<string, string> */
    protected function validationAttributeLabels(): array
    {
        return app(BillCollectionPaymentService::class)->validationAttributeLabels();
    }
}
