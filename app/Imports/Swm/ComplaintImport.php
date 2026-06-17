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
        $columnDefinitions = $service->importColumnDefinitions();
        $complaintTypes = config('swm_complaints.complaint_types', []);
        $submittedThrough = config('swm_complaints.submitted_through', []);
        $complaintStatuses = config('swm_complaints.complaint_statuses', []);

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
                if ($name === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'name', $columnDefinitions);
                    continue;
                }

                $contactNumber = trim((string) ($norm['contact_number'] ?? ''));
                if ($contactNumber === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'contact_number', $columnDefinitions);
                    continue;
                }

                $complaintTypeInput = trim((string) ($norm['complaint_type'] ?? ''));
                $complaintType = SwmImportRowHelper::resolveConfigKey(
                    $complaintTypeInput,
                    $complaintTypes
                );
                if ($complaintType === null) {
                    $this->errors[] = $complaintTypeInput === ''
                        ? SwmImportRowHelper::rowRequired($rowNum, 'complaint_type', $columnDefinitions)
                        : SwmImportRowHelper::rowInvalid($rowNum, 'complaint_type', $columnDefinitions);
                    continue;
                }

                $complaintDetails = trim((string) ($norm['complaint_details'] ?? ''));
                if ($complaintDetails === '') {
                    $this->errors[] = SwmImportRowHelper::rowRequired($rowNum, 'complaint_details', $columnDefinitions);
                    continue;
                }

                $submittedThroughInput = trim((string) ($norm['submitted_through'] ?? ''));
                $submittedThroughKey = SwmImportRowHelper::resolveConfigKey(
                    $submittedThroughInput,
                    $submittedThrough
                );
                if ($submittedThroughKey === null) {
                    $this->errors[] = $submittedThroughInput === ''
                        ? SwmImportRowHelper::rowRequired($rowNum, 'submitted_through', $columnDefinitions)
                        : SwmImportRowHelper::rowInvalid($rowNum, 'submitted_through', $columnDefinitions);
                    continue;
                }

                $complaintStatusInput = trim((string) ($norm['complaint_status'] ?? ''));
                $complaintStatus = SwmImportRowHelper::resolveConfigKey(
                    $complaintStatusInput,
                    $complaintStatuses
                );
                if ($complaintStatus === null) {
                    $this->errors[] = $complaintStatusInput === ''
                        ? SwmImportRowHelper::rowRequired($rowNum, 'complaint_status', $columnDefinitions)
                        : SwmImportRowHelper::rowInvalid($rowNum, 'complaint_status', $columnDefinitions);
                    continue;
                }

                $complaintStatusOther = null;
                if ($complaintStatus === 'others') {
                    $complaintStatusOther = trim((string) ($norm['complaint_status_other'] ?? ''));
                    if ($complaintStatusOther === '') {
                        $this->errors[] = SwmImportRowHelper::rowRequiredWhen(
                            $rowNum,
                            'complaint_status_other',
                            $columnDefinitions,
                            'complaint_status',
                            (string) ($complaintStatuses['others'] ?? __('others'))
                        );
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
                    $this->errors[] = SwmImportRowHelper::rowMessage($rowNum, __('could not save.'));
                }
            } catch (\Throwable $e) {
                $this->errors[] = SwmImportRowHelper::importCatchMessage($rowNum, $e);
            }
        }
    }
}
