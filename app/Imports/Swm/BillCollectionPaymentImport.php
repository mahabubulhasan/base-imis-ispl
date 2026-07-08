<?php

namespace App\Imports\Swm;

use App\Models\BuildingInfo\Household;
use App\Services\Swm\BillCollectionPaymentService;
use App\Support\Swm\SwmImportRowHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class BillCollectionPaymentImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $defaultReceivedByUserId)
    {
    }

    public function sheets(): array
    {
        return [0 => $this];   // process ONLY the first sheet
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
            if (SwmImportRowHelper::rowHasNoRequiredData($norm, $columnDefinitions)) {
                continue;
            }

            try {
                $site = $this->resolveSite($norm);
                if (! $site) {
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('could not resolve household.'));
                    continue;
                }
                $holding = isset($norm['holding_number']) ? trim((string) $norm['holding_number']) : '';
                if ($holding !== '' && ($site->holding_number ?? '') !== $holding) {
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('holding number does not match site.'));
                    continue;
                }

                $wardRaw = isset($norm['ward']) ? trim((string) $norm['ward']) : '';
                if ($wardRaw === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'ward', $columnDefinitions);
                    continue;
                }
                if (! is_numeric($wardRaw) || (int) $wardRaw !== (int) ($site->ward ?? 0)) {
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('ward does not match household.'));
                    continue;
                }

                $amountRaw = $norm['amount'] ?? null;
                if ($amountRaw === null || trim((string) $amountRaw) === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'amount', $columnDefinitions);
                    continue;
                }
                $amount = SwmImportRowHelper::parseDecimal($amountRaw);
                if ($amount === null) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'amount', $columnDefinitions);
                    continue;
                }
                $duePaid = SwmImportRowHelper::parseDecimal($norm['due_paid'] ?? null) ?? 0.0;

                $transactionMonth = $this->parseMonth($norm['transaction_month'] ?? null);
                if (! $transactionMonth) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'transaction_month', $columnDefinitions);
                    continue;
                }
                $maxTransactionMonth = now()->startOfMonth();
                if ($transactionMonth->gt($maxTransactionMonth)) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid(
                        $rowNum, 'transaction_month', $columnDefinitions,
                        __('cannot be later than :month', ['month' => $maxTransactionMonth->format('M Y')])
                    );
                    continue;
                }

                $methodRaw = trim((string) ($norm['payment_method'] ?? ''));
                $methodKey = null;
                if ($methodRaw !== '') {
                    $methodKey = SwmImportRowHelper::resolveConfigKey($methodRaw, $paymentMethods);
                    if ($methodKey === null) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'payment_method', $columnDefinitions);
                        continue;
                    }
                }

                $paymentTime = SwmImportRowHelper::parseDate($norm['payment_time'] ?? null) ?? now();
                $recvId = $this->resolveReceivedByUserId($norm['received_by_user_id'] ?? null);

                $data = [
                    'household_id' => $site->id,
                    'amount' => $amount,
                    'due_paid' => $duePaid,
                    'transaction_month' => $transactionMonth->format('Y-m-d'),
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
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('could not save.'));
                }
            } catch (\Throwable $e) {
                $this->errors[] = SwmImportRowHelper::importCatchMessage($rowNum, $e);
            }
        }
    }

    protected function resolveSite(array $norm): ?Household
    {
        $input = trim((string) ($norm['household_id'] ?? ''));
        if ($input === '') {
            return null;
        }

        // Backward compatibility: older templates encoded the household as "code - {id}".
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
