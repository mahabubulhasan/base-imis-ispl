<?php

namespace App\Imports\Swm;

use App\Enums\SwmOrganizationStatus;
use App\Models\Swm\OrganizationType;
use App\Services\Swm\OrganizationService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrganizationImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(OrganizationService::class);
        $columnDefinitions = $service->importColumnDefinitions();
        $orgTypeMap = OrganizationType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
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
                $email = trim((string) ($norm['email'] ?? ''));
                $address = trim((string) ($norm['address'] ?? ''));
                $contactPersonName = trim((string) ($norm['contact_person_name'] ?? ''));
                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));

                if ($name === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'name', $columnDefinitions);
                    continue;
                }
                if ($email === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'email', $columnDefinitions);
                    continue;
                }
                if ($address === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'address', $columnDefinitions);
                    continue;
                }
                if ($contactPersonName === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'contact_person_name', $columnDefinitions);
                    continue;
                }
                if ($contactNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'contact_number', $columnDefinitions);
                    continue;
                }

                $orgTypeId = SwmImportRowHelper::resolveByLabel(
                    trim((string) ($norm['organization_type'] ?? '')),
                    $orgTypeMap
                );
                if (! $orgTypeId) {
                    $this->errors[] = SwmImportRowHelper::rowInvalid($rowNum, 'organization_type', $columnDefinitions);
                    continue;
                }

                $status = $this->resolveOrganizationStatus($norm['status'] ?? null);
                if ($status === null) {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'status', $columnDefinitions);
                    continue;
                }

                $data = [
                    'name' => $name,
                    'email' => $email,
                    'address' => $address,
                    'contact_person_name' => $contactPersonName,
                    'contact_number' => $contactNumber,
                    'organization_type_id' => $orgTypeId,
                    'service_wards' => SwmImportRowHelper::parseCommaSeparatedInts(
                        isset($norm['service_wards']) ? (string) $norm['service_wards'] : null
                    ),
                    'remarks' => ($norm['remarks'] ?? '') !== '' ? (string) $norm['remarks'] : null,
                    'status' => $status,
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

    protected function resolveOrganizationStatus($value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        $parsed = SwmImportRowHelper::parseBoolean($value);
        if ($parsed !== null) {
            return $parsed;
        }

        $input = trim((string) $value);
        if (strcasecmp($input, SwmOrganizationStatus::getDescription(true)) === 0) {
            return true;
        }
        if (strcasecmp($input, SwmOrganizationStatus::getDescription(false)) === 0) {
            return false;
        }

        return null;
    }
}
