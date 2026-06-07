<?php

namespace App\Imports\Swm;

use App\Models\Swm\Organization;
use App\Models\Swm\WorkType;
use App\Services\Swm\WorkerService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WorkerImport implements ToCollection, WithHeadingRow
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
        $service = app(WorkerService::class);
        $orgMap = Organization::query()
            ->whereNull('deleted_at')
            ->operational()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
        $workTypeMap = WorkType::query()
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

                $workTypeId = SwmImportRowHelper::resolveByLabel(
                    trim((string) ($norm['work_type'] ?? '')),
                    $workTypeMap
                );
                if (! $workTypeId) {
                    $this->errors[] = __('Row :n: work_type is required.', ['n' => $rowNum]);
                    continue;
                }

                $name = trim((string) ($norm['name'] ?? ''));
                $mobile = trim((string) ($norm['mobile'] ?? ''));
                if ($name === '') {
                    $this->errors[] = __('Row :n: name is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($mobile === '') {
                    $this->errors[] = __('Row :n: mobile is required.', ['n' => $rowNum]);
                    continue;
                }

                $gender = $this->resolveGender($norm['gender'] ?? null);
                $employmentType = $this->resolveEmploymentType($norm['employment_type'] ?? null);
                $status = $this->resolveWorkerStatus($norm['status'] ?? null);

                $age = $norm['age'] ?? null;
                if ($age !== null && $age !== '') {
                    $age = (int) $age;
                } else {
                    $age = null;
                }

                $data = [
                    'organization_id' => $orgId,
                    'work_type_id' => $workTypeId,
                    'name' => $name,
                    'mobile' => $mobile,
                    'email' => ($norm['email'] ?? '') !== '' ? trim((string) $norm['email']) : null,
                    'age' => $age,
                    'gender' => $gender,
                    'service_wards' => SwmImportRowHelper::parseCommaSeparatedInts(
                        isset($norm['service_wards']) ? (string) $norm['service_wards'] : null
                    ),
                    'employment_type' => $employmentType,
                    'status' => $status ?? 'active',
                    'employee_id' => ($norm['employee_id'] ?? '') !== '' ? trim((string) $norm['employee_id']) : null,
                    'national_id_no' => ($norm['national_id_no'] ?? '') !== '' ? trim((string) $norm['national_id_no']) : null,
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

    protected function resolveGender($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $key = strtolower(trim((string) $value));

        return in_array($key, ['male', 'female', 'others'], true) ? $key : null;
    }

    protected function resolveEmploymentType($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $key = strtolower(trim((string) $value));

        return in_array($key, ['permanent', 'daily', 'contract'], true) ? $key : null;
    }

    protected function resolveWorkerStatus($value): ?string
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
