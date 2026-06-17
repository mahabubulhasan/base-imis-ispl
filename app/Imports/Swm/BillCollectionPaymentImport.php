<?php

namespace App\Imports\Swm;

use App\Models\BuildingInfo\Household;
use App\Services\Swm\BillCollectionPaymentService;
use App\Support\Swm\SwmImportRowHelper;
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
        $columnDefinitions = $service->importColumnDefinitions();
        $paymentMethods = config('bill_collection.payment_methods', []);

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::mapRowToKeys(
                SwmImportRowHelper::normalizeRow($row->toArray()),
                $columnDefinitions
            );
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $site = $this->resolveSite($norm);
                if (! $site) {
                    $this->errors[] = __('Row :n: could not resolve household.', ['n' => $rowNum]);
                    continue;
                }
                $holding = isset($norm['holding_number']) ? trim((string) $norm['holding_number']) : '';
                if ($holding !== '' && ($site->holding_number ?? '') !== $holding) {
                    $this->errors[] = __('Row :n: holding_number does not match site.', ['n' => $rowNum]);
                    continue;
                }

                $amount = $norm['amount'] ?? null;
                if ($amount === null || $amount === '') {
                    $this->errors[] = __('Row :n: amount is required.', ['n' => $rowNum]);
                    continue;
                }
                $duePaid = $norm['due_paid'] ?? 0;
                if ($duePaid === null || $duePaid === '') {
                    $duePaid = 0;
                }

                $month = $this->parseMonth($norm['payment_for_month'] ?? null);
                if (! $month) {
                    $this->errors[] = __('Row :n: payment_for_month is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $methodKey = SwmImportRowHelper::resolveConfigKey(
                    trim((string) ($norm['payment_method'] ?? '')),
                    $paymentMethods
                );
                if ($methodKey === null) {
                    $this->errors[] = __('Row :n: invalid payment_method.', ['n' => $rowNum]);
                    continue;
                }

                $paymentTime = SwmImportRowHelper::parseDate($norm['payment_time'] ?? null) ?? now();
                $recvId = $this->resolveReceivedByUserId($norm['received_by_user_id'] ?? null);

                $data = [
                    'household_id' => $site->id,
                    'amount' => $amount,
                    'due_paid' => $duePaid,
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

    protected function resolveSite(array $norm): ?Household
    {
        $input = trim((string) ($norm['household_id'] ?? ''));
        if ($input === '') {
            return null;
        }

        if (preg_match('/ - (\d+)$/', $input, $matches)) {
            $id = (int) $matches[1];
            if ($id > 0) {
                return Household::query()
                    ->whereKey($id)
                    ->whereNull('deleted_at')
                    ->activeStatus()
                    ->first();
            }
        }

        if (is_numeric($input)) {
            $id = (int) $input;
            if ($id > 0) {
                return Household::query()
                    ->whereKey($id)
                    ->whereNull('deleted_at')
                    ->activeStatus()
                    ->first();
            }
        }

        return Household::query()
            ->where('household_id', $input)
            ->whereNull('deleted_at')
            ->activeStatus()
            ->first();
    }

    protected function resolveReceivedByUserId(mixed $value): int
    {
        if ($value === null || $value === '') {
            return $this->defaultReceivedByUserId;
        }

        $input = trim((string) $value);
        if (preg_match('/ - (\d+)$/', $input, $matches)) {
            return (int) $matches[1];
        }

        if (is_numeric($input)) {
            return (int) $input;
        }

        return $this->defaultReceivedByUserId;
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
            }
        }

        return SwmImportRowHelper::parseMonth($value);
    }
}
