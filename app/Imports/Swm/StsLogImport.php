<?php

namespace App\Imports\Swm;

use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Services\Swm\StsLogService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StsLogImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $service = app(StsLogService::class);
        $columnDefinitions = [
            ['key' => 'entry_at', 'label' => __('Entry Date and Time')],
            ['key' => 'operation_date', 'label' => __('Operation Date')],
            ['key' => 'vehicle_number', 'label' => __('Vehicle Number')],
            ['key' => 'sts_name', 'label' => __('STS Name')],
            ['key' => 'waste_types', 'label' => __('Waste Type')],
            ['key' => 'quantity_ton', 'label' => __('Quantity (Ton)')],
            ['key' => 'source_wards', 'label' => __('Source Wards')],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ];
        $vehicleMap = Vehicle::query()
            ->whereNull('deleted_at')
            ->orderBy('vehicle_number')
            ->pluck('vehicle_number', 'id')
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

                $stsLabel = trim((string) ($norm['sts_name'] ?? ''));
                if ($stsLabel === '') {
                    $this->errors[] = __('Row :n: sts_name is required.', ['n' => $rowNum]);
                    continue;
                }
                $stsId = $this->resolveStsId($stsLabel, $stsLabelMap);
                if (! $stsId) {
                    $this->errors[] = __('Row :n: invalid sts_name.', ['n' => $rowNum]);
                    continue;
                }
                $sts = Sts::query()->whereKey($stsId)->first();
                $stsName = $sts?->name ?? $stsLabel;

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
                $data['sts_id'] = $stsId;
                $data['sts_name'] = $stsName;

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

    protected function resolveStsId(string $label, array $labelMap): ?int
    {
        $id = SwmImportRowHelper::resolveByLabel($label, $labelMap);
        if ($id) {
            return (int) $id;
        }

        $sts = Sts::query()
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(name) = ?', [strtolower($label)])
            ->first();

        return $sts ? (int) $sts->id : null;
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
