<?php

namespace App\Imports;

use App\Models\BuildingInfo\Household;
use App\Models\Swm\WasteBinType;
use App\Services\Swm\WasteBinService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WasteBinImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(WasteBinService::class);
        $wasteBinTypeMap = WasteBinType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $typeName = trim((string) ($norm['waste_bin_type'] ?? ''));
                if ($typeName === '') {
                    $this->errors[] = __('Row :n: waste_bin_type is required.', ['n' => $rowNum]);
                    continue;
                }
                $wasteBinTypeId = SwmImportRowHelper::resolveByLabel($typeName, $wasteBinTypeMap);
                if (! $wasteBinTypeId) {
                    $this->errors[] = __('Row :n: unknown waste_bin_type.', ['n' => $rowNum]);
                    continue;
                }

                $capacity = $norm['total_capacity_kg'] ?? null;
                if ($capacity === null || $capacity === '') {
                    $this->errors[] = __('Row :n: total_capacity_kg is required.', ['n' => $rowNum]);
                    continue;
                }

                $placed = SwmImportRowHelper::parseBoolean($norm['placed_at_buildings'] ?? null) ?? false;

                $householdId = null;
                $householdRaw = trim((string) ($norm['household_id'] ?? ''));
                if ($householdRaw !== '') {
                    if (is_numeric($householdRaw)) {
                        $household = Household::query()
                            ->whereKey((int) $householdRaw)
                            ->whereNull('deleted_at')
                            ->first();
                        if (! $household) {
                            $this->errors[] = __('Row :n: household_id not found.', ['n' => $rowNum]);
                            continue;
                        }
                        $householdId = (int) $household->id;
                    } else {
                        $this->errors[] = __('Row :n: household_id must be numeric.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $wardNo = null;
                $wardRaw = trim((string) ($norm['ward_no'] ?? ''));
                if ($wardRaw !== '') {
                    if (! is_numeric($wardRaw)) {
                        $this->errors[] = __('Row :n: ward_no must be numeric.', ['n' => $rowNum]);
                        continue;
                    }
                    $wardNo = (int) $wardRaw;
                }

                $data = [
                    'waste_bin_type_id' => $wasteBinTypeId,
                    'placed_at_buildings' => $placed,
                    'household_id' => $householdId,
                    'bin' => $placed ? (trim((string) ($norm['bin'] ?? '')) ?: null) : null,
                    'ward_no' => $wardNo,
                    'sub_location' => trim((string) ($norm['sub_location'] ?? '')) ?: null,
                    'road_no' => trim((string) ($norm['road_no'] ?? '')) ?: null,
                    'road_name' => trim((string) ($norm['road_name'] ?? '')) ?: null,
                    'latitude' => ($norm['latitude'] ?? '') !== '' ? $norm['latitude'] : null,
                    'longitude' => ($norm['longitude'] ?? '') !== '' ? $norm['longitude'] : null,
                    'total_capacity_kg' => $capacity,
                ];

                $service->storeOrUpdate(null, $data);
                $this->successCount++;
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
            }
        }
    }
}
