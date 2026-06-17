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
        $columnDefinitions = [
            ['key' => 'name', 'label' => __('Landfill Name')],
            ['key' => 'location', 'label' => __('Location')],
            ['key' => 'operator_name', 'label' => __('Operator Name')],
            ['key' => 'contact_number', 'label' => __("Operator's Contact Number")],
            ['key' => 'capacity', 'label' => __('Capacity').' ('.__('Ton').')'],
            ['key' => 'area', 'label' => __('Area').' ('.__('Acre').')'],
            ['key' => 'landfill_type', 'label' => __('Landfill Type')],
            ['key' => 'source_sts', 'label' => __('Source STSs')],
            ['key' => 'source_wards', 'label' => __('Other Source Wards')],
            ['key' => 'segregation_practiced', 'label' => __('Segregation Practiced?')],
            ['key' => 'waste_types', 'label' => __('Waste Type')],
            ['key' => 'weighbridge_facility_available', 'label' => __('Weighbridge Facility Available?')],
            ['key' => 'boundary_wall_available', 'label' => __('Boundary Wall Around the Landfill Area Available?')],
            ['key' => 'lighting_arrangement_available', 'label' => __('Lighting Arrangement at the Landfill Site Available?')],
            ['key' => 'manpower_deployed', 'label' => __('Number of Manpower Deployed at the Landfill Site')],
            ['key' => 'adequate_covering_arrangement_available', 'label' => __('Adequate Covering Arrangement at the Landfill Site Available?')],
            ['key' => 'gas_control_system_available', 'label' => __('System for Gas Control from the Filled Landfill Available?')],
            ['key' => 'leachate_collection_system_available', 'label' => __('Leachate Collection System Available?')],
            ['key' => 'operational_status', 'label' => __('Operational Status')],
        ];
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
                    $this->errors[] = __('Row :n: name is required.', ['n' => $rowNum]);
                    continue;
                }

                $operatorName = trim((string) ($norm['operator_name'] ?? ''));
                if ($operatorName === '') {
                    $this->errors[] = __('Row :n: operator_name is required.', ['n' => $rowNum]);
                    continue;
                }

                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                if ($contactNumber === '' || ! preg_match('/^[0-9]+$/', $contactNumber)) {
                    $this->errors[] = __('Row :n: contact_number is required and must be numeric.', ['n' => $rowNum]);
                    continue;
                }

                $operationalStatus = SwmImportRowHelper::resolveEnumKey(
                    trim((string) ($norm['operational_status'] ?? 'active')),
                    ['active', 'inactive']
                );
                if (! $operationalStatus) {
                    $this->errors[] = __('Row :n: invalid operational_status.', ['n' => $rowNum]);
                    continue;
                }

                $landfillTypeId = null;
                $typeName = trim((string) ($norm['landfill_type'] ?? ''));
                if ($typeName !== '') {
                    $landfillTypeId = SwmImportRowHelper::resolveByLabel($typeName, $landfillTypeMap);
                    if (! $landfillTypeId) {
                        $this->errors[] = __('Row :n: unknown landfill_type.', ['n' => $rowNum]);
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
                    $this->errors[] = __('Row :n: could not save.', ['n' => $rowNum]);
                }
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
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
                $this->errors[] = __('Row :n: unknown source STS ":sts".', ['n' => $rowNum, 'sts' => $label]);

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
                $this->errors[] = __('Row :n: unknown waste type ":type".', ['n' => $rowNum, 'type' => $name]);

                return false;
            }
            $ids[] = $id;
        }

        return array_values(array_unique($ids));
    }
}
