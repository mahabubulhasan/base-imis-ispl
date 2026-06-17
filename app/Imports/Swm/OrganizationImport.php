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
        $columnDefinitions = [
            ['key' => 'name', 'label' => __('Organization Name')],
            ['key' => 'email', 'label' => __('Email')],
            ['key' => 'address', 'label' => __('Address')],
            ['key' => 'contact_person_name', 'label' => __('Contact Person Name')],
            ['key' => 'contact_number', 'label' => __('Contact Number')],
            ['key' => 'organization_type', 'label' => __('Organization Type')],
            ['key' => 'service_wards', 'label' => __('Service Wards')],
            ['key' => 'remarks', 'label' => __('Remarks')],
            ['key' => 'status', 'label' => __('Status')],
        ];
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
                    $this->errors[] = __('Row :n: name is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($email === '') {
                    $this->errors[] = __('Row :n: email is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($address === '') {
                    $this->errors[] = __('Row :n: address is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($contactPersonName === '') {
                    $this->errors[] = __('Row :n: contact_person_name is required.', ['n' => $rowNum]);
                    continue;
                }
                if ($contactNumber === '') {
                    $this->errors[] = __('Row :n: contact_number is required.', ['n' => $rowNum]);
                    continue;
                }

                $orgTypeId = SwmImportRowHelper::resolveByLabel(
                    trim((string) ($norm['organization_type'] ?? '')),
                    $orgTypeMap
                );
                if (! $orgTypeId) {
                    $this->errors[] = __('Row :n: invalid organization_type.', ['n' => $rowNum]);
                    continue;
                }

                $status = $this->resolveOrganizationStatus($norm['status'] ?? null);
                if ($status === null) {
                    $this->errors[] = __('Row :n: status is required.', ['n' => $rowNum]);
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
                    $this->errors[] = __('Row :n: could not save.', ['n' => $rowNum]);
                }
            } catch (\Throwable $e) {
                $this->errors[] = __('Row :n: :msg', ['n' => $rowNum, 'msg' => $e->getMessage()]);
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
