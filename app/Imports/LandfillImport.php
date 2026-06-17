<?php

namespace App\Imports;

use App\Models\Swm\LandfillType;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Services\Swm\LandfillService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LandfillImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(LandfillService::class);
        $columnDefinitions = $service->importColumnDefinitions();
        $landfillTypeMap = LandfillType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $stsLabelMap = Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Sts $sts) {
                $label = trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name);

                return [$sts->id => $label];
            })
            ->all();

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
                $name = trim((string) ($norm['name'] ?? ''));
                if ($name === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'name', $columnDefinitions);
                    continue;
                }

                $operatorName = trim((string) ($norm['operator_name'] ?? ''));
                if ($operatorName === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'operator_name', $columnDefinitions);
                    continue;
                }

                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                if ($contactNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'contact_number', $columnDefinitions);
                    continue;
                }
                if (! preg_match('/^[0-9]+$/', $contactNumber)) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid(
                        $rowNum,
                        'contact_number',
                        $columnDefinitions,
                        (string) __('must be numeric')
                    );
                    continue;
                }

                $operationalStatus = SwmImportRowHelper::resolveEnumKey(
                    trim((string) ($norm['operational_status'] ?? 'active')),
                    ['active', 'inactive']
                );
                if (! $operationalStatus) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'operational_status', $columnDefinitions);
                    continue;
                }

                $landfillTypeId = null;
                $typeName = trim((string) ($norm['landfill_type'] ?? ''));
                if ($typeName !== '') {
                    $landfillTypeId = SwmImportRowHelper::resolveByLabel($typeName, $landfillTypeMap);
                    if (! $landfillTypeId) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'landfill_type', $columnDefinitions);
                        continue;
                    }
                }

                $sourceStsIds = $this->resolveSourceStsIds($norm['source_sts'] ?? null, $stsLabelMap, $rowNum);
                if ($sourceStsIds === false) {
                    continue;
                }

                $wasteTypeIds = $this->resolveWasteTypeIds($norm['waste_types'] ?? null, $wasteTypeMap, $rowNum);
                if ($wasteTypeIds === false) {
                    continue;
                }

                $sourceWards = SwmImportRowHelper::parseCommaSeparatedInts(
                    isset($norm['source_wards']) ? (string) $norm['source_wards'] : null
                );

                $data = [
                    'name' => $name,
                    'location' => trim((string) ($norm['location'] ?? '')) ?: null,
                    'operator_name' => $operatorName,
                    'contact_number' => $contactNumber,
                    'capacity' => ($norm['capacity'] ?? '') !== '' ? $norm['capacity'] : null,
                    'area' => ($norm['area'] ?? '') !== '' ? $norm['area'] : null,
                    'landfill_type_id' => $landfillTypeId,
                    'source_sts_ids' => $sourceStsIds,
                    'source_wards' => $sourceWards,
                    'segregation_practiced' => SwmImportRowHelper::parseBoolean($norm['segregation_practiced'] ?? null),
                    'waste_type_ids' => $wasteTypeIds,
                    'weighbridge_facility_available' => SwmImportRowHelper::parseBoolean($norm['weighbridge_facility_available'] ?? null),
                    'boundary_wall_available' => SwmImportRowHelper::parseBoolean($norm['boundary_wall_available'] ?? null),
                    'lighting_arrangement_available' => SwmImportRowHelper::parseBoolean($norm['lighting_arrangement_available'] ?? null),
                    'manpower_deployed' => ($norm['manpower_deployed'] ?? '') !== '' ? (int) $norm['manpower_deployed'] : null,
                    'adequate_covering_arrangement_available' => SwmImportRowHelper::parseBoolean($norm['adequate_covering_arrangement_available'] ?? null),
                    'gas_control_system_available' => SwmImportRowHelper::parseBoolean($norm['gas_control_system_available'] ?? null),
                    'leachate_collection_system_available' => SwmImportRowHelper::parseBoolean($norm['leachate_collection_system_available'] ?? null),
                    'operational_status' => $operationalStatus,
                ];

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

    /**
     * @param  array<int|string, string>  $stsLabelMap
     * @return array<int, int>|null|false
     */
    protected function resolveSourceStsIds(mixed $value, array $stsLabelMap, int $rowNum): array|null|false
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $labels = array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== '');
        if (empty($labels)) {
            return null;
        }

        $ids = [];
        foreach ($labels as $label) {
            $id = SwmImportRowHelper::resolveByLabel($label, $stsLabelMap);
            if (! $id) {
                $this->errors[] = SwmImportRowHelper::rowMessage(
                    $rowNum,
                    __('unknown source STS ":sts".', ['sts' => $label])
                );

                return false;
            }
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param  array<int|string, string>  $wasteTypeMap
     * @return array<int, int>|null|false
     */
    protected function resolveWasteTypeIds(mixed $value, array $wasteTypeMap, int $rowNum): array|null|false
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $names = array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== '');
        if (empty($names)) {
            return null;
        }

        $ids = [];
        foreach ($names as $name) {
            $id = SwmImportRowHelper::resolveByLabel($name, $wasteTypeMap);
            if (! $id) {
                $this->errors[] = SwmImportRowHelper::rowMessage(
                    $rowNum,
                    __('unknown waste type ":type".', ['type' => $name])
                );

                return false;
            }
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }
}
