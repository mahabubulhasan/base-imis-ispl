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

class AttendanceLogImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(AttendanceLogService::class);
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
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $orgId = $scopedOrgId;
                if (! $orgId) {
                    $orgLabel = trim((string) ($norm['organization'] ?? ''));
                    if ($orgLabel === '') {
                        $this->errors[] = __('Row :n: organization is required.', ['n' => $rowNum]);
                        continue;
                    }
                    $orgId = SwmImportRowHelper::resolveByLabel($orgLabel, $orgMap);
                    if (! $orgId) {
                        $this->errors[] = __('Row :n: invalid organization.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $workerLabel = trim((string) ($norm['worker'] ?? ''));
                if ($workerLabel === '') {
                    $this->errors[] = __('Row :n: worker is required.', ['n' => $rowNum]);
                    continue;
                }
                $workerId = $this->resolveWorkerId($workerLabel, $orgId);
                if (! $workerId) {
                    $this->errors[] = __('Row :n: invalid worker.', ['n' => $rowNum]);
                    continue;
                }

                $entryAt = SwmImportRowHelper::parseDate($norm['entry_at'] ?? null);
                if (! $entryAt) {
                    $this->errors[] = __('Row :n: entry_at is invalid.', ['n' => $rowNum]);
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
                    $this->errors[] = __('Row :n: invalid attendance_status.', ['n' => $rowNum]);
                    continue;
                }

                $checkIn = SwmImportRowHelper::parseDate($norm['check_in_at'] ?? null);
                $checkOut = SwmImportRowHelper::parseDate($norm['check_out_at'] ?? null);

                if ($status === AttendanceLog::STATUS_PRESENT && ! $checkIn) {
                    $this->errors[] = __('Row :n: check_in_at is required when present.', ['n' => $rowNum]);
                    continue;
                }
                if ($checkIn && $checkOut && $checkOut->lt($checkIn)) {
                    $this->errors[] = __('Row :n: check_out_at must be on or after check_in_at.', ['n' => $rowNum]);
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
                    $this->errors[] = __('Row :n: could not save.', ['n' => $rowNum]);
                }
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
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
