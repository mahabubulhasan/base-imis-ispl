<?php

namespace App\Imports;

use App\Models\Swm\Landfill;
use App\Models\Swm\WasteType;
use App\Services\Swm\StsService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StsImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(StsService::class);
        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $landfillMap = Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Landfill $landfill) {
                $label = trim(($landfill->landfill_id ? $landfill->landfill_id.' - ' : '').$landfill->name);

                return [$landfill->id => $label];
            })
            ->all();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $name = trim((string) ($norm['name'] ?? ''));
                if ($name === '') {
                    $this->errors[] = __('Row :n: name is required.', ['n' => $rowNum]);
                    continue;
                }

                $wardRaw = trim((string) ($norm['ward_no'] ?? ''));
                if ($wardRaw === '' || ! is_numeric($wardRaw)) {
                    $this->errors[] = __('Row :n: ward_no is required.', ['n' => $rowNum]);
                    continue;
                }
                $wardNo = (int) $wardRaw;

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

                $wasteTypeIds = $this->resolveWasteTypeIds($norm['waste_types'] ?? null, $wasteTypeMap, $rowNum);
                if ($wasteTypeIds === false) {
                    continue;
                }

                $destinationLandfillId = null;
                $landfillLabel = trim((string) ($norm['destination_landfill'] ?? ''));
                if ($landfillLabel !== '') {
                    $destinationLandfillId = SwmImportRowHelper::resolveByLabel($landfillLabel, $landfillMap);
                    if (! $destinationLandfillId) {
                        $this->errors[] = __('Row :n: unknown destination_landfill.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $sourceWards = SwmImportRowHelper::parseCommaSeparatedInts(
                    isset($norm['source_wards']) ? (string) $norm['source_wards'] : null
                );

                $data = [
                    'name' => $name,
                    'location' => trim((string) ($norm['location'] ?? '')) ?: null,
                    'ward_no' => $wardNo,
                    'road_id' => trim((string) ($norm['road_id'] ?? '')) ?: null,
                    'road_name' => trim((string) ($norm['road_name'] ?? '')) ?: null,
                    'latitude' => ($norm['latitude'] ?? '') !== '' ? $norm['latitude'] : null,
                    'longitude' => ($norm['longitude'] ?? '') !== '' ? $norm['longitude'] : null,
                    'operator_name' => $operatorName,
                    'contact_number' => $contactNumber,
                    'capacity' => trim((string) ($norm['capacity'] ?? '')) ?: null,
                    'area' => ($norm['area'] ?? '') !== '' ? $norm['area'] : null,
                    'source_wards' => $sourceWards,
                    'segregation_practiced' => SwmImportRowHelper::parseBoolean($norm['segregation_practiced'] ?? null) ?? false,
                    'waste_type_ids' => $wasteTypeIds,
                    'destination_landfill_id' => $destinationLandfillId,
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
