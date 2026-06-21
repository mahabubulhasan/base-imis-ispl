<?php

namespace App\Imports\Swm;

use App\Models\Swm\Organization;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\VehicleType;
use App\Models\Swm\Worker;
use App\Services\Swm\VehicleService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VehicleImport implements ToCollection, WithHeadingRow, WithMultipleSheets
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
        $scopedOrganizationId = Auth::user()?->swm_organization_id
            ? (int) Auth::user()->swm_organization_id
            : null;
        $service = app(VehicleService::class);
        $columnDefinitions = $service->importColumnDefinitions();
        $orgMap = Organization::query()
            ->whereNull('deleted_at')
            ->operational()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $vehicleTypeMap = VehicleType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $operationalKeys = array_keys(VehicleService::operationalTypeLabels());
        $stsMap = Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Sts $sts) {
                $label = trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name);

                return [$sts->id => $label];
            })
            ->all();
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
                if ($scopedOrganizationId) {
                    $orgId = $scopedOrganizationId;
                } else {
                    $orgId = SwmImportRowHelper::resolveByLabel(
                        trim((string) ($norm['organization'] ?? '')),
                        $orgMap
                    );
                    if (! $orgId) {
                        $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'organization', $columnDefinitions);
                        continue;
                    }
                }

                $vehicleTypeId = SwmImportRowHelper::resolveByLabel(
                    trim((string) ($norm['vehicle_type'] ?? '')),
                    $vehicleTypeMap
                );
                if (! $vehicleTypeId) {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'vehicle_type', $columnDefinitions);
                    continue;
                }

                $vehicleNumber = trim((string) ($norm['vehicle_number'] ?? ''));
                if ($vehicleNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'vehicle_number', $columnDefinitions);
                    continue;
                }

                $driverWorkerId = $this->resolveDriverWorkerId(
                    trim((string) ($norm['driver'] ?? '')),
                    $orgId
                );
                if (! $driverWorkerId) {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'driver', $columnDefinitions);
                    continue;
                }

                $operationalType = SwmImportRowHelper::resolveEnumKey(
                    trim((string) ($norm['operational_type'] ?? '')),
                    $operationalKeys
                );
                if (($norm['operational_type'] ?? '') !== '' && $operationalType === null) {
                    $labels = VehicleService::operationalTypeLabels();
                    foreach ($labels as $key => $label) {
                        if (strcasecmp(trim((string) $norm['operational_type']), (string) $label) === 0) {
                            $operationalType = $key;
                            break;
                        }
                    }
                }

                $status = $this->resolveVehicleStatus($norm['status'] ?? null);
                $dumpingKind = $this->resolveDumpingKind($norm['dumping_place_kind'] ?? null);
                if (! $dumpingKind) {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'dumping_place_kind', $columnDefinitions);
                    continue;
                }

                $dumpingStsId = null;
                $dumpingLandfillId = null;
                $dumpingOther = ($norm['dumping_place_other'] ?? '') !== '' ? trim((string) $norm['dumping_place_other']) : null;

                if ($dumpingKind === 'sts') {
                    $dumpingStsId = $this->resolveByLabelOrId(trim((string) ($norm['dumping_sts_id'] ?? '')), $stsMap);
                    if (! $dumpingStsId) {
                        $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                            $rowNum,
                            'dumping_sts_id',
                            $columnDefinitions,
                            'dumping_place_kind',
                            (string) __('STS')
                        );
                        continue;
                    }
                }

                if ($dumpingKind === 'landfill') {
                    $dumpingLandfillId = $this->resolveByLabelOrId(trim((string) ($norm['dumping_landfill_id'] ?? '')), $landfillMap);
                    if (! $dumpingLandfillId) {
                        $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                            $rowNum,
                            'dumping_landfill_id',
                            $columnDefinitions,
                            'dumping_place_kind',
                            (string) __('Landfill')
                        );
                        continue;
                    }
                }

                if ($dumpingKind === 'other' && ($dumpingOther === null || $dumpingOther === '')) {
                    $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                        $rowNum,
                        'dumping_place_other',
                        $columnDefinitions,
                        'dumping_place_kind',
                        (string) __('Others (specify)')
                    );
                    continue;
                }

                $data = [
                    'organization_id' => $orgId,
                    'vehicle_type_id' => $vehicleTypeId,
                    'vehicle_number' => $vehicleNumber,
                    'vehicle_id_no' => ($norm['vehicle_id_no'] ?? '') !== '' ? trim((string) $norm['vehicle_id_no']) : null,
                    'driver_worker_id' => $driverWorkerId,
                    'chassis_no' => ($norm['chassis_no'] ?? '') !== '' ? trim((string) $norm['chassis_no']) : null,
                    'engine_no' => ($norm['engine_no'] ?? '') !== '' ? trim((string) $norm['engine_no']) : null,
                    'capacity' => ($norm['capacity'] ?? '') !== '' ? trim((string) $norm['capacity']) : null,
                    'operational_type' => $operationalType,
                    'operational_type_other' => ($norm['operational_type_other'] ?? '') !== '' ? trim((string) $norm['operational_type_other']) : null,
                    'service_wards' => SwmImportRowHelper::parseCommaSeparatedInts(
                        isset($norm['service_wards']) ? (string) $norm['service_wards'] : null
                    ),
                    'dumping_place_kind' => $dumpingKind,
                    'dumping_sts_id' => $dumpingStsId,
                    'dumping_landfill_id' => $dumpingLandfillId,
                    'dumping_place_other' => $dumpingOther,
                    'fuel_type' => ($norm['fuel_type'] ?? '') !== '' ? trim((string) $norm['fuel_type']) : null,
                    'status' => $status ?? 'active',
                    'last_maintenance_year' => ($norm['last_maintenance_year'] ?? '') !== '' ? (int) $norm['last_maintenance_year'] : null,
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? trim((string) $norm['remarks']) : null,
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

    protected function resolveDriverWorkerId(string $driverName, int $orgId): ?int
    {
        if ($driverName === '') {
            return null;
        }

        $wtId = VehicleService::driverWorkTypeId();
        if (! $wtId) {
            return null;
        }

        $baseQuery = Worker::query()
            ->where('work_type_id', $wtId)
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId);

        if (is_numeric($driverName)) {
            $id = (int) $driverName;
            if ((clone $baseQuery)->whereKey($id)->exists()) {
                return $id;
            }
        }

        $worker = (clone $baseQuery)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($driverName)])
            ->first();

        return $worker ? (int) $worker->id : null;
    }

    protected function resolveVehicleStatus($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $key = strtolower(trim((string) $value));
        if (in_array($key, ['active', 'inactive'], true)) {
            return $key;
        }

        return SwmImportRowHelper::parseBoolean($value) === false ? 'inactive' : 'active';
    }

    protected function resolveDumpingKind(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $resolved = SwmImportRowHelper::resolveEnumKey((string) $value, ['sts', 'landfill', 'other']);
        if ($resolved) {
            return $resolved;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return match ($normalized) {
            mb_strtolower((string) __('STS')) => 'sts',
            mb_strtolower((string) __('Landfill')) => 'landfill',
            mb_strtolower((string) __('Others (specify)')) => 'other',
            default => null,
        };
    }

    protected function resolveByLabelOrId(string $value, array $map): ?int
    {
        if ($value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        $resolved = SwmImportRowHelper::resolveByLabel($value, $map);

        return $resolved ? (int) $resolved : null;
    }
}
