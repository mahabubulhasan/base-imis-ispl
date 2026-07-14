<?php

namespace App\Imports;

use App\Models\Swm\WasteBinType;
use App\Services\Swm\WasteBinService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WasteBinImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function sheets(): array
    {
        return [0 => $this];   // process ONLY the first sheet
    }

    public function collection(Collection $rows): void
    {
        $service = app(WasteBinService::class);
        $columnDefinitions = $service->importColumnDefinitions();
        $wasteBinTypeMap = WasteBinType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();

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
                $typeName = trim((string) ($norm['waste_bin_type'] ?? ''));
                if ($typeName === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'waste_bin_type', $columnDefinitions);
                    continue;
                }
                $wasteBinTypeId = SwmImportRowHelper::resolveByLabel($typeName, $wasteBinTypeMap);
                if (! $wasteBinTypeId) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'waste_bin_type', $columnDefinitions);
                    continue;
                }

                $capacityRaw = $norm['total_capacity_kg'] ?? null;
                if ($capacityRaw === null || trim((string) $capacityRaw) === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'total_capacity_kg', $columnDefinitions);
                    continue;
                }
                $capacity = SwmImportRowHelper::parseDecimal($capacityRaw);
                if ($capacity === null) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'total_capacity_kg', $columnDefinitions);
                    continue;
                }

                $placed = SwmImportRowHelper::parseBoolean($norm['placed_at_buildings'] ?? null) ?? false;

                $wardNo = null;
                $wardRaw = trim((string) ($norm['ward_no'] ?? ''));
                if ($wardRaw !== '') {
                    if (! is_numeric($wardRaw)) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid(
                            $rowNum,
                            'ward_no',
                            $columnDefinitions,
                            (string) __('must be numeric')
                        );
                        continue;
                    }
                    $wardNo = (int) $wardRaw;
                }

                $data = [
                    'waste_bin_type_id' => $wasteBinTypeId,
                    'placed_at_buildings' => $placed,
                    'household_id' => null,
                    'bin' => $placed ? (trim((string) ($norm['bin'] ?? '')) ?: null) : null,
                    'ward_no' => $wardNo,
                    'sub_location' => trim((string) ($norm['sub_location'] ?? '')) ?: null,
                    'road_no' => trim((string) ($norm['road_no'] ?? '')) ?: null,
                    'road_name' => trim((string) ($norm['road_name'] ?? '')) ?: null,
                    'latitude' => SwmImportRowHelper::parseDecimal($norm['latitude'] ?? null),
                    'longitude' => SwmImportRowHelper::parseDecimal($norm['longitude'] ?? null),
                    'total_capacity_kg' => $capacity,
                ];

                $service->storeOrUpdate(null, $data);
                $this->successCount++;
            } catch (\Throwable $e) {
                $this->errors[] = SwmImportRowHelper::importCatchMessage($rowNum, $e);
            }
        }
    }
}
