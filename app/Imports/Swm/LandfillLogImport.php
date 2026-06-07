<?php

namespace App\Imports\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Services\Swm\LandfillLogService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LandfillLogImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $service = app(LandfillLogService::class);
        $vehicleMap = Vehicle::query()
            ->whereNull('deleted_at')
            ->orderBy('vehicle_number')
            ->pluck('vehicle_number', 'id')
            ->all();
        $landfillMap = Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $vehicleLabel = trim((string) ($norm['vehicle_number'] ?? ''));
                if ($vehicleLabel === '') {
                    $this->errors[] = __('Row :n: vehicle_number is required.', ['n' => $rowNum]);
                    continue;
                }
                $vehicleId = SwmImportRowHelper::resolveByLabel($vehicleLabel, $vehicleMap)
                    ?? $this->resolveVehicleIdByNumber($vehicleLabel);
                if (! $vehicleId) {
                    $this->errors[] = __('Row :n: invalid vehicle_number.', ['n' => $rowNum]);
                    continue;
                }

                $entryAt = SwmImportRowHelper::parseDate($norm['entry_at'] ?? null);
                if (! $entryAt) {
                    $this->errors[] = __('Row :n: entry_at is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $operationDate = SwmImportRowHelper::parseDate($norm['operation_date'] ?? null);
                if (! $operationDate) {
                    $this->errors[] = __('Row :n: operation_date is invalid.', ['n' => $rowNum]);
                    continue;
                }

                $landfillId = null;
                $landfillName = null;
                $landfillLabel = trim((string) ($norm['landfill_name'] ?? ''));
                if ($landfillLabel !== '') {
                    $landfillId = SwmImportRowHelper::resolveByLabel($landfillLabel, $landfillMap);
                    if (! $landfillId) {
                        $this->errors[] = __('Row :n: invalid landfill_name.', ['n' => $rowNum]);
                        continue;
                    }
                    $landfillName = $landfillMap[$landfillId] ?? $landfillLabel;
                }

                $wasteTypeIds = $this->resolveWasteTypeIds($norm['waste_types'] ?? null);
                $sourceWards = $this->parseSourceWards($norm['source_wards'] ?? null);

                $data = [
                    'vehicle_id' => $vehicleId,
                    'entry_at' => $entryAt->format('Y-m-d H:i:s'),
                    'operation_date' => $operationDate->format('Y-m-d'),
                    'quantity_ton' => ($norm['quantity_ton'] ?? '') !== '' ? $norm['quantity_ton'] : null,
                    'source_wards' => $sourceWards,
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? (string) $norm['remarks'] : null,
                    'waste_type_ids' => $wasteTypeIds,
                ];
                if ($landfillId) {
                    $data['landfill_id'] = $landfillId;
                    $data['landfill_name'] = $landfillName;
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

    protected function resolveVehicleIdByNumber(string $number): ?int
    {
        $vehicle = Vehicle::query()
            ->whereNull('deleted_at')
            ->where('vehicle_number', $number)
            ->first();

        return $vehicle ? (int) $vehicle->id : null;
    }

    /** @return array<int> */
    protected function resolveWasteTypeIds(?string $input): array
    {
        if ($input === null || trim((string) $input) === '') {
            return [];
        }

        $names = array_filter(array_map('trim', explode(',', (string) $input)), fn ($v) => $v !== '');
        $ids = [];
        foreach ($names as $name) {
            $type = WasteType::query()
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->first();
            if ($type) {
                $ids[] = (int) $type->id;
            }
        }

        return array_values(array_unique($ids));
    }

    /** @return list<string>|null */
    protected function parseSourceWards(?string $input): ?array
    {
        if ($input === null || trim((string) $input) === '') {
            return null;
        }

        return array_values(array_unique(array_filter(
            array_map('trim', explode(',', (string) $input)),
            fn ($v) => $v !== ''
        )));
    }
}
