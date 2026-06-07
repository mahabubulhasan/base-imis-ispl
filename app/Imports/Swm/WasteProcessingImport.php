<?php

namespace App\Imports\Swm;

use App\Services\Swm\WasteProcessingService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WasteProcessingImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $service = app(WasteProcessingService::class);

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $entryAt = SwmImportRowHelper::parseDate($norm['entry_at'] ?? null);
                if (! $entryAt) {
                    $this->errors[] = __('Row :n: entry_at is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $reportDate = SwmImportRowHelper::parseDate($norm['report_date'] ?? null);
                if (! $reportDate) {
                    $this->errors[] = __('Row :n: report_date is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $reportingMonth = SwmImportRowHelper::parseMonth($norm['reporting_month'] ?? null);
                if (! $reportingMonth) {
                    $this->errors[] = __('Row :n: reporting_month is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $data = [
                    'entry_at' => $entryAt->format('Y-m-d H:i:s'),
                    'report_date' => $reportDate->format('Y-m-d'),
                    'reporting_month' => $reportingMonth->format('Y-m-d'),
                    'waste_processing_site_name' => ($norm['waste_processing_site_name'] ?? '') !== ''
                        ? (string) $norm['waste_processing_site_name']
                        : null,
                    'waste_received_ton' => $this->nullableNumeric($norm['waste_received_ton'] ?? null),
                    'organic_waste_composted_ton' => $this->nullableNumeric($norm['organic_waste_composted_ton'] ?? null),
                    'inorganic_waste_recycled_ton' => $this->nullableNumeric($norm['inorganic_waste_recycled_ton'] ?? null),
                    'waste_incinerated_ton' => $this->nullableNumeric($norm['waste_incinerated_ton'] ?? null),
                    'waste_burned_open_air_ton' => $this->nullableNumeric($norm['waste_burned_open_air_ton'] ?? null),
                    'residual_waste_landfilled_ton' => $this->nullableNumeric($norm['residual_waste_landfilled_ton'] ?? null),
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? (string) $norm['remarks'] : null,
                ];

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

    protected function nullableNumeric(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
