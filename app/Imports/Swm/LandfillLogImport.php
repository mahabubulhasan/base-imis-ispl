<?php

namespace App\Imports\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
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
        $columnDefinitions = $service->importColumnDefinitions();
        $vehicleMap = Vehicle::query()
            ->whereNull('deleted_at')
            ->orderBy('vehicle_number')
            ->pluck('vehicle_number', 'id')
            ->all();
        $landfillLabelMap = Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Landfill $landfill) {
                $label = trim(($landfill->landfill_id ? $landfill->landfill_id.' - ' : '').$landfill->name);

                return [$landfill->id => $label];
            })
            ->all();
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
                $vehicleLabel = trim((string) ($norm['vehicle_number'] ?? ''));
                if ($vehicleLabel === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'vehicle_number', $columnDefinitions);
                    continue;
                }
                $vehicleId = SwmImportRowHelper::resolveByLabel($vehicleLabel, $vehicleMap)
                    ?? $this->resolveVehicleIdByNumber($vehicleLabel);
                if (! $vehicleId) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'vehicle_number', $columnDefinitions);
                    continue;
                }

                $entryAt = SwmImportRowHelper::parseDate($norm['entry_at'] ?? null);
                if (! $entryAt) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'entry_at', $columnDefinitions);
                    continue;
                }

                $operationDate = SwmImportRowHelper::parseDate($norm['operation_date'] ?? null);
                if (! $operationDate) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'operation_date', $columnDefinitions);
                    continue;
                }

                $landfillId = null;
                $landfillName = null;
                $landfillLabel = trim((string) ($norm['landfill_name'] ?? ''));
                if ($landfillLabel !== '') {
                    $landfillId = $this->resolveLandfillId($landfillLabel, $landfillLabelMap);
                    if (! $landfillId) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'landfill_name', $columnDefinitions);
                        continue;
                    }
                    $landfill = Landfill::query()->whereKey($landfillId)->first();
                    $landfillName = $landfill?->name ?? $landfillLabel;
                }

                $wasteTypeIds = $this->resolveWasteTypeIds($norm['waste_types'] ?? null);
                $sourceStsIds = $this->resolveSourceStsIds($norm['source_sts'] ?? null, $stsLabelMap, $rowNum);
                if ($sourceStsIds === false) {
                    continue;
                }
                $sourceWards = $this->parseSourceWards($norm['source_wards'] ?? null);

                $data = [
                    'vehicle_id' => $vehicleId,
                    'entry_at' => $entryAt->format('Y-m-d H:i:s'),
                    'operation_date' => $operationDate->format('Y-m-d'),
                    'quantity_ton' => ($norm['quantity_ton'] ?? '') !== '' ? $norm['quantity_ton'] : null,
                    'source_sts_ids' => $sourceStsIds,
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
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('could not save.'));
                }
            } catch (\Throwable $e) {
                $this->errors[] = SwmImportRowHelper::importCatchMessage($rowNum, $e);
            }
        }
    }

    protected function resolveLandfillId(string $label, array $labelMap): ?int
    {
        if (is_numeric($label)) {
            $numericId = (int) $label;
            if (Landfill::query()->whereNull('deleted_at')->whereKey($numericId)->exists()) {
                return $numericId;
            }
        }

        $id = SwmImportRowHelper::resolveByLabel($label, $labelMap);
        if ($id) {
            return (int) $id;
        }

        $landfill = Landfill::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(name) = ?', [strtolower($label)])
            ->first();

        return $landfill ? (int) $landfill->id : null;
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

    /** @return array<int>|null|false */
    protected function resolveSourceStsIds(mixed $input, array $stsLabelMap, int $rowNum): array|null|false
    {
        if ($input === null || trim((string) $input) === '') {
            return null;
        }

        $labels = array_filter(array_map('trim', explode(',', (string) $input)), fn ($v) => $v !== '');
        if (empty($labels)) {
            return null;
        }

        $ids = [];
        foreach ($labels as $label) {
            $id = null;
            if (is_numeric($label)) {
                $numericId = (int) $label;
                if (Sts::query()->whereNull('deleted_at')->whereKey($numericId)->exists()) {
                    $id = $numericId;
                }
            }
            if (! $id) {
                $resolved = SwmImportRowHelper::resolveByLabel($label, $stsLabelMap);
                $id = $resolved ? (int) $resolved : null;
            }
            if (! $id) {
                $sts = Sts::query()
                    ->whereNull('deleted_at')
                    ->whereRaw('LOWER(name) = ?', [strtolower($label)])
                    ->first();
                $id = $sts ? (int) $sts->id : null;
            }
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
