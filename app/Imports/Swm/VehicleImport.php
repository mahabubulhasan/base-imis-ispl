<?php

namespace App\Imports\Swm;

use App\Models\Swm\Organization;
use App\Models\Swm\VehicleType;
use App\Models\Swm\Worker;
use App\Services\Swm\VehicleService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VehicleImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $scopedOrganizationId = Auth::user()?->swm_organization_id
            ? (int) Auth::user()->swm_organization_id
            : null;
        $service = app(VehicleService::class);
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

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
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
                        $this->errors[] = __('Row :n: organization is required.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $vehicleTypeId = SwmImportRowHelper::resolveByLabel(
                    trim((string) ($norm['vehicle_type'] ?? '')),
                    $vehicleTypeMap
                );
                if (! $vehicleTypeId) {
                    $this->errors[] = __('Row :n: vehicle_type is required.', ['n' => $rowNum]);
                    continue;
                }

                $vehicleNumber = trim((string) ($norm['vehicle_number'] ?? ''));
                if ($vehicleNumber === '') {
                    $this->errors[] = __('Row :n: vehicle_number is required.', ['n' => $rowNum]);
                    continue;
                }

                $driverWorkerId = $this->resolveDriverWorkerId(
                    trim((string) ($norm['driver'] ?? '')),
                    $orgId
                );
                if (! $driverWorkerId) {
                    $this->errors[] = __('Row :n: driver is required.', ['n' => $rowNum]);
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
                    'service_wards' => SwmImportRowHelper::parseCommaSeparatedInts(
                        isset($norm['service_wards']) ? (string) $norm['service_wards'] : null
                    ),
                    'status' => $status ?? 'active',
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
}
