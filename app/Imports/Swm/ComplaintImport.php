<?php

namespace App\Imports\Swm;

use App\Services\Swm\ComplaintService;
use App\Support\Swm\SwmImportRowHelper;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ComplaintImport implements ToCollection, WithHeadingRow
{
    public int $successCount = 0;

    /** @var array<int, string> */
    public array $errors = [];

    public function __construct(private int $importedByUserId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(ComplaintService::class);
        $complaintTypes = config('swm_complaints.complaint_types', []);
        $submittedThrough = config('swm_complaints.submitted_through', []);
        $complaintStatuses = config('swm_complaints.complaint_statuses', []);

        foreach ($rows as $idx => $row) {
            $rowNum = $idx + 2;
            $norm = SwmImportRowHelper::normalizeRow($row->toArray());
            if (SwmImportRowHelper::rowIsEmpty($norm)) {
                continue;
            }

            try {
                $name = trim((string) ($norm['name'] ?? ''));
                if ($name === '') {
                    $this->errors[] = __('Row :n: name is required.', ['n' => $rowNum]);
                    continue;
                }

                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                if ($contactNumber === '') {
                    $this->errors[] = __('Row :n: contact_number is required.', ['n' => $rowNum]);
                    continue;
                }

                $complaintType = SwmImportRowHelper::resolveConfigKey(
                    trim((string) ($norm['complaint_type'] ?? '')),
                    $complaintTypes
                );
                if ($complaintType === null) {
                    $this->errors[] = __('Row :n: complaint_type is required or invalid.', ['n' => $rowNum]);
                    continue;
                }

                $complaintDetails = trim((string) ($norm['complaint_details'] ?? ''));
                if ($complaintDetails === '') {
                    $this->errors[] = __('Row :n: complaint_details is required.', ['n' => $rowNum]);
                    continue;
                }

                $submittedThroughKey = SwmImportRowHelper::resolveConfigKey(
                    trim((string) ($norm['submitted_through'] ?? '')),
                    $submittedThrough
                );
                if ($submittedThroughKey === null) {
                    $this->errors[] = __('Row :n: submitted_through is required or invalid.', ['n' => $rowNum]);
                    continue;
                }

                $complaintStatus = SwmImportRowHelper::resolveConfigKey(
                    trim((string) ($norm['complaint_status'] ?? '')),
                    $complaintStatuses
                );
                if ($complaintStatus === null) {
                    $this->errors[] = __('Row :n: complaint_status is required or invalid.', ['n' => $rowNum]);
                    continue;
                }

                $complaintStatusOther = null;
                if ($complaintStatus === 'others') {
                    $complaintStatusOther = trim((string) ($norm['complaint_status_other'] ?? ''));
                    if ($complaintStatusOther === '') {
                        $this->errors[] = __('Row :n: complaint_status_other is required when complaint_status is others.', ['n' => $rowNum]);
                        continue;
                    }
                }

                $dateTime = SwmImportRowHelper::parseDate($norm['date_time'] ?? null);
                $incidentDate = SwmImportRowHelper::parseDate($norm['incident_date'] ?? null);
                $duplicateComplaint = SwmImportRowHelper::parseBoolean($norm['duplicate_complaint'] ?? null) ?? false;

                $data = [
                    'name' => $name,
                    'contact_number' => $contactNumber,
                    'complaint_type' => $complaintType,
                    'complaint_details' => $complaintDetails,
                    'submitted_through' => $submittedThroughKey,
                    'complaint_status' => $complaintStatus,
                    'complaint_status_other' => $complaintStatusOther,
                    'duplicate_complaint' => $duplicateComplaint,
                ];

                if ($dateTime) {
                    $data['date_time'] = $dateTime->format('Y-m-d H:i:s');
                }
                if ($incidentDate) {
                    $data['incident_date'] = $incidentDate->format('Y-m-d');
                }
                if (isset($norm['holding_number']) && trim((string) $norm['holding_number']) !== '') {
                    $data['holding_number'] = trim((string) $norm['holding_number']);
                }
                if (isset($norm['household_id']) && trim((string) $norm['household_id']) !== '') {
                    $data['household_id'] = trim((string) $norm['household_id']);
                }
                if (isset($norm['ward_no']) && trim((string) $norm['ward_no']) !== '') {
                    $data['ward_no'] = trim((string) $norm['ward_no']);
                }
                if (isset($norm['duplicate_reference']) && trim((string) $norm['duplicate_reference']) !== '') {
                    $data['duplicate_reference'] = trim((string) $norm['duplicate_reference']);
                }
                if (isset($norm['priority_level']) && $norm['priority_level'] !== '') {
                    $data['priority_level'] = (int) $norm['priority_level'];
                }
                if (isset($norm['assigned_to']) && trim((string) $norm['assigned_to']) !== '') {
                    $data['assigned_to'] = trim((string) $norm['assigned_to']);
                }
                if (isset($norm['resolution_time_days']) && $norm['resolution_time_days'] !== '') {
                    $data['resolution_time_days'] = (int) $norm['resolution_time_days'];
                }
                if (isset($norm['notes']) && trim((string) $norm['notes']) !== '') {
                    $data['notes'] = trim((string) $norm['notes']);
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
}
