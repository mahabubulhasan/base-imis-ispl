<?php

namespace App\Imports\Swm;

use App\Models\Swm\AttendanceLog;
use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Models\User;
use App\Services\Swm\AttendanceLogService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceLogImport implements ToCollection, WithHeadingRow, WithMultipleSheets
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
        $service = app(AttendanceLogService::class);
        $columnDefinitions = $service->importColumnDefinitions();
        $user = User::query()->find($this->userId);
        $scopedOrgId = $user?->swm_organization_id ? (int) $user->swm_organization_id : null;
        $orgMap = Organization::query()
            ->whereNull('deleted_at')
            ->operational()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $statusKeys = [
            AttendanceLog::STATUS_PRESENT,
            AttendanceLog::STATUS_ABSENT,
            AttendanceLog::STATUS_ON_LEAVE,
        ];

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
                $orgId = $scopedOrgId;
                if (! $orgId) {
                    $orgLabel = trim((string) ($norm['organization'] ?? ''));
                    if ($orgLabel === '') {
                        $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'organization', $columnDefinitions);
                        continue;
                    }
                    $orgId = SwmImportRowHelper::resolveByLabel($orgLabel, $orgMap);
                    if (! $orgId) {
                        $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'organization', $columnDefinitions);
                        continue;
                    }
                }

                $workerLabel = trim((string) ($norm['worker'] ?? ''));
                if ($workerLabel === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'worker', $columnDefinitions);
                    continue;
                }
                $workerId = $this->resolveWorkerId($workerLabel, $orgId);
                if (! $workerId) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'worker', $columnDefinitions);
                    continue;
                }

                $entryAt = SwmImportRowHelper::parseDate($norm['entry_at'] ?? null);
                if (! $entryAt) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'entry_at', $columnDefinitions);
                    continue;
                }

                $statusInput = trim((string) ($norm['attendance_status'] ?? ''));
                $status = SwmImportRowHelper::resolveEnumKey($statusInput, $statusKeys);
                if (! $status) {
                    foreach (AttendanceLog::statusOptions() as $key => $label) {
                        if (strcasecmp($statusInput, (string) $label) === 0) {
                            $status = $key;
                            break;
                        }
                    }
                }
                if (! $status) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'attendance_status', $columnDefinitions);
                    continue;
                }

                $checkIn = SwmImportRowHelper::parseDate($norm['check_in_at'] ?? null);
                $checkOut = SwmImportRowHelper::parseDate($norm['check_out_at'] ?? null);

                if ($status === AttendanceLog::STATUS_PRESENT && ! $checkIn) {
                    $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                        $rowNum,
                        'check_in_at',
                        $columnDefinitions,
                        'attendance_status',
                        (string) (AttendanceLog::statusOptions()[AttendanceLog::STATUS_PRESENT] ?? __('present'))
                    );
                    continue;
                }
                if ($checkIn && $checkOut && $checkOut->lt($checkIn)) {
                    $this->errors[] = SwmImportRowHelper::rowMessage(
                        $rowNum,
                        __('check-out time must be on or after check-in time.')
                    );
                    continue;
                }

                $data = [
                    'organization_id' => $orgId,
                    'worker_id' => $workerId,
                    'department' => ($norm['department'] ?? '') !== '' ? (string) $norm['department'] : null,
                    'entry_at' => $entryAt->format('Y-m-d H:i:s'),
                    'attendance_status' => $status,
                    'check_in_at' => $checkIn?->format('Y-m-d H:i:s'),
                    'check_out_at' => $checkOut?->format('Y-m-d H:i:s'),
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? (string) $norm['remarks'] : null,
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

    protected function resolveWorkerId(string $input, int $orgId): ?int
    {
        $workers = Worker::query()
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId)
            ->orderBy('name')
            ->get(['id', 'name', 'worker_id_no']);

        foreach ($workers as $worker) {
            $label = $worker->name.($worker->worker_id_no ? ' — '.$worker->worker_id_no : '');
            if (strcasecmp(trim($input), $label) === 0) {
                return (int) $worker->id;
            }
        }

        if (is_numeric($input)) {
            $worker = Worker::query()
                ->whereNull('deleted_at')
                ->where('organization_id', $orgId)
                ->whereKey((int) $input)
                ->first();

            return $worker ? (int) $worker->id : null;
        }

        return null;
    }
}
