<?php

namespace App\Imports;

use App\Models\BuildingInfo\Household;
use App\Services\Swm\BillCollectionPaymentService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BillCollectionPaymentImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $defaultReceivedByUserId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(BillCollectionPaymentService::class);
        $methodKeys = array_keys(config('bill_collection.payment_methods', []));

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $raw = $row->toArray();
            $norm = [];
            foreach ($raw as $k => $v) {
                $norm[strtolower(trim((string) $k))] = $v;
            }
            if ($this->rowIsEmpty($norm)) {
                continue;
            }
            try {
                $site = $this->resolveSite($norm);
                if (! $site) {
                    $this->errors[] = __('Row :n: could not resolve household.', ['n' => $rowNum]);
                    continue;
                }
                $holding = isset($norm['holding_number']) ? trim((string) $norm['holding_number']) : '';
                $cid = trim((string) ($norm['household_id'] ?? ''));
                if ($holding !== '' && ($site->holding_number ?? '') !== $holding) {
                    $this->errors[] = __('Row :n: holding_number does not match site.', ['n' => $rowNum]);
                    continue;
                }
                if ($cid !== '' && (string) $site->household_id !== $cid) {
                    $this->errors[] = __('Row :n: household_id does not match site.', ['n' => $rowNum]);
                    continue;
                }

                $amount = $norm['amount'] ?? null;
                if ($amount === null || $amount === '') {
                    $this->errors[] = __('Row :n: amount is required.', ['n' => $rowNum]);
                    continue;
                }

                $month = $this->parseMonth($norm['payment_for_month'] ?? null);
                if (! $month) {
                    $this->errors[] = __('Row :n: payment_for_month is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $methodKey = $this->resolveMethodKey(trim((string) ($norm['payment_method'] ?? '')), $methodKeys);
                if ($methodKey === null) {
                    $this->errors[] = __('Row :n: invalid payment_method.', ['n' => $rowNum]);
                    continue;
                }

                $paymentTime = $this->parseDateTime($norm['payment_time'] ?? null) ?? now();
                $recv = $norm['received_by_user_id'] ?? null;
                $recvId = ($recv !== null && $recv !== '') ? (int) $recv : $this->defaultReceivedByUserId;

                $data = [
                    'household_id' => $site->id,
                    'amount' => $amount,
                    'payment_for_month' => $month->format('Y-m-d'),
                    'payment_time' => $paymentTime,
                    'payment_method' => $methodKey,
                    'received_by_user_id' => $recvId,
                ];
                if (array_key_exists('receipt_no', $norm)) {
                    $rn = trim((string) ($norm['receipt_no'] ?? ''));
                    $data['receipt_no'] = $rn !== '' ? $rn : null;
                }
                $saved = $service->storeOrUpdate(null, $data);
                if ($saved) {
                    $this->successCount++;
                } else {
                    $this->errors[] = __('Row :n: could not save.', ['n' => $rowNum]);
                }
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
            }
        }
    }

    protected function rowIsEmpty(array $norm): bool
    {
        foreach ($norm as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }

        return true;
    }

    protected function resolveSite(array $norm): ?Household
    {
        $siteId = $norm['household_id'] ?? null;
        if ($siteId !== null && $siteId !== '') {
            $id = (int) $siteId;
            if ($id > 0) {
                return Household::query()
                    ->whereKey($id)
                    ->whereNull('deleted_at')
                    ->activeStatus()
                    ->first();
            }
        }
        $customerId = trim((string) ($norm['household_id'] ?? ''));

        return Household::query()
            ->where('household_id', $customerId)
            ->whereNull('deleted_at')
            ->activeStatus()
            ->first();
    }

    protected function parseMonth($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value) && (float) $value > 20000) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject((float) $value))->startOfMonth();
            } catch (\Throwable $e) {
                // fall through
            }
        }
        try {
            return Carbon::parse($value)->startOfMonth();
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseDateTime($value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value) && (float) $value > 1) {
            try {
                return Carbon::instance(Date::excelToDateTimeObject((float) $value));
            } catch (\Throwable $e) {
            }
        }
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param  array<int, string>  $methodKeys
     */
    protected function resolveMethodKey(string $input, array $methodKeys): ?string
    {
        if ($input === '') {
            return null;
        }
        $key = strtolower($input);
        if (in_array($key, $methodKeys, true)) {
            return $key;
        }
        $labels = config('bill_collection.payment_methods', []);
        foreach ($labels as $k => $label) {
            if (strcasecmp($input, (string) $label) === 0) {
                return $k;
            }
        }

        return null;
    }
}
