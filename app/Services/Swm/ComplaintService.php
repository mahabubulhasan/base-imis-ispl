<?php

namespace App\Services\Swm;

use App\Models\Swm\Complaint;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelExportWriter;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class ComplaintService
{
    use HasExcelColumnValidationLabels;

    public function getAllComplaints(array $data)
    {
        $query = Complaint::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['complaint_id'] ?? null)) {
                    $q->where('complaint_id', 'ILIKE', '%'.trim((string) $data['complaint_id']).'%');
                }
                if (! empty($data['holding_number'] ?? null)) {
                    $q->where('holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
                }
                if (! empty($data['household_id'] ?? null)) {
                    $q->where('customer_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
                }
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (! empty($data['ward_no'] ?? null)) {
                    $q->where('ward_no', 'ILIKE', '%'.trim((string) $data['ward_no']).'%');
                }
                if (! empty($data['complaint_type'] ?? null)) {
                    $q->where('complaint_type', $data['complaint_type']);
                }
                if (! empty($data['submitted_through'] ?? null)) {
                    $q->where('submitted_through', $data['submitted_through']);
                }
                if (! empty($data['complaint_status'] ?? null)) {
                    $q->where('complaint_status', $data['complaint_status']);
                }
                if (isset($data['duplicate_complaint']) && $data['duplicate_complaint'] !== '') {
                    $q->where('duplicate_complaint', filter_var($data['duplicate_complaint'], FILTER_VALIDATE_BOOLEAN));
                }
                if (! empty($data['priority_level'] ?? null)) {
                    $q->where('priority_level', (int) $data['priority_level']);
                }
                if (! empty($data['assigned_to'] ?? null)) {
                    $q->where('assigned_to', 'ILIKE', '%'.trim((string) $data['assigned_to']).'%');
                }
                if (! empty($data['date_from'] ?? null)) {
                    $q->whereDate('date_time', '>=', Carbon::parse($data['date_from'])->toDateString());
                }
                if (! empty($data['date_to'] ?? null)) {
                    $q->whereDate('date_time', '<=', Carbon::parse($data['date_to'])->toDateString());
                }
                if (! empty($data['incident_date_from'] ?? null)) {
                    $q->whereDate('incident_date', '>=', Carbon::parse($data['incident_date_from'])->toDateString());
                }
                if (! empty($data['incident_date_to'] ?? null)) {
                    $q->whereDate('incident_date', '<=', Carbon::parse($data['incident_date_to'])->toDateString());
                }
            })
            ->editColumn('date_time', function ($model) {
                return $model->date_time?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('incident_date', function ($model) {
                return $model->incident_date?->format('Y-m-d') ?? '';
            })
            ->editColumn('complaint_type', function ($model) {
                $map = config('swm_complaints.complaint_types', []);

                return $map[$model->complaint_type] ?? $model->complaint_type;
            })
            ->editColumn('submitted_through', function ($model) {
                $map = config('swm_complaints.submitted_through', []);

                return $map[$model->submitted_through] ?? $model->submitted_through;
            })
            ->editColumn('complaint_status', function ($model) {
                $map = config('swm_complaints.complaint_statuses', []);
                if ($model->complaint_status === 'others' && ! empty($model->complaint_status_other)) {
                    return ($map['others'] ?? __('Other')).': '.$model->complaint_status_other;
                }

                return $map[$model->complaint_status] ?? $model->complaint_status;
            })
            ->addColumn('duplicate_complaint_text', function ($model) {
                return $model->duplicate_complaint ? __('Yes') : __('No');
            })
            ->addColumn('duplicate_complaint_id', function ($model) {
                return (string) ($model->duplicate_reference ?? '');
            })
            ->addColumn('household_id', function ($model) {
                return $model->customer_id;
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.complaints.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Complaint')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\ComplaintController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Complaint')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\ComplaintController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Complaint History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\ComplaintController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Complaint')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function storeOrUpdate(?int $id, array $data): ?int
    {
        if (is_null($id)) {
            $complaint = new Complaint();
        } else {
            $complaint = Complaint::find($id);
            if (! $complaint) {
                return null;
            }
        }

        if (! empty($data['date_time'] ?? null)) {
            $dateTime = Carbon::parse($data['date_time']);
        } elseif ($complaint->exists) {
            $dateTime = $complaint->date_time
                ? Carbon::parse($complaint->date_time)
                : now();
        } else {
            $dateTime = now();
        }
        $photo = $data['photo_attachment'] ?? null;
        $newPhotoPath = null;
        if ($photo instanceof UploadedFile) {
            $newPhotoPath = $photo->store('swm/complaints', 'public');
        }

        DB::transaction(function () use ($complaint, $data, $id, $dateTime, $newPhotoPath): void {
            if (is_null($id)) {
                $complaint->complaint_id = $this->nextComplaintIdForMonth($dateTime);
            }

            $complaint->date_time = $dateTime;
            $complaint->holding_number = $data['holding_number'] ?? null;
            $complaint->customer_id = $data['household_id'] ?? null;
            $complaint->name = $data['name'] ?? null;
            $complaint->contact_number = $data['contact_number'] ?? null;
            $complaint->ward_no = $data['ward_no'] ?? null;
            $complaint->incident_date = $data['incident_date'] ?? null;
            $complaint->complaint_type = $data['complaint_type'] ?? null;
            $complaint->complaint_details = $data['complaint_details'] ?? null;
            $complaint->duplicate_complaint = (bool) ($data['duplicate_complaint'] ?? false);
            $complaint->duplicate_reference = $data['duplicate_reference'] ?? null;
            $complaint->priority_level = $data['priority_level'] ?? null;
            $complaint->assigned_to = $data['assigned_to'] ?? null;
            $complaint->submitted_through = $data['submitted_through'] ?? null;
            $complaint->complaint_status = $data['complaint_status'] ?? null;
            $complaint->complaint_status_other = ($data['complaint_status'] ?? null) === 'others'
                ? ($data['complaint_status_other'] ?? null)
                : null;
            $complaint->resolution_time_days = $data['resolution_time_days'] ?? null;
            if ($newPhotoPath !== null) {
                if (! empty($complaint->photo_attachment_path)) {
                    Storage::disk('public')->delete($complaint->photo_attachment_path);
                }
                $complaint->photo_attachment_path = $newPhotoPath;
            }
            $complaint->notes = $data['notes'] ?? null;
            $complaint->save();
        });

        return $complaint->id;
    }

    private function nextComplaintIdForMonth(Carbon $dateTime): string
    {
        $ym = $dateTime->format('Ym');
        $prefix = "CMP-{$ym}-";

        $lastId = Complaint::query()
            ->where('complaint_id', 'LIKE', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('complaint_id')
            ->value('complaint_id');

        $nextNumber = 1;
        if (is_string($lastId) && preg_match('/^CMP-\d{6}-(\d{4})$/', $lastId, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function download(array $data): void
    {
        $query = Complaint::query()->whereNull('deleted_at');

        if (! empty($data['complaint_id'] ?? null)) {
            $query->where('complaint_id', 'ILIKE', '%'.trim((string) $data['complaint_id']).'%');
        }
        if (! empty($data['holding_number'] ?? null)) {
            $query->where('holding_number', 'ILIKE', '%'.trim((string) $data['holding_number']).'%');
        }
        if (! empty($data['household_id'] ?? null)) {
            $query->where('customer_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
        }
        if (! empty($data['name'] ?? null)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['ward_no'] ?? null)) {
            $query->where('ward_no', 'ILIKE', '%'.trim((string) $data['ward_no']).'%');
        }
        if (! empty($data['complaint_type'] ?? null)) {
            $query->where('complaint_type', $data['complaint_type']);
        }
        if (! empty($data['submitted_through'] ?? null)) {
            $query->where('submitted_through', $data['submitted_through']);
        }
        if (! empty($data['complaint_status'] ?? null)) {
            $query->where('complaint_status', $data['complaint_status']);
        }
        if (isset($data['duplicate_complaint']) && $data['duplicate_complaint'] !== '') {
            $query->where('duplicate_complaint', filter_var($data['duplicate_complaint'], FILTER_VALIDATE_BOOLEAN));
        }
        if (! empty($data['priority_level'] ?? null)) {
            $query->where('priority_level', (int) $data['priority_level']);
        }
        if (! empty($data['assigned_to'] ?? null)) {
            $query->where('assigned_to', 'ILIKE', '%'.trim((string) $data['assigned_to']).'%');
        }
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('date_time', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('date_time', '<=', Carbon::parse($data['date_to'])->toDateString());
        }
        if (! empty($data['incident_date_from'] ?? null)) {
            $query->whereDate('incident_date', '>=', Carbon::parse($data['incident_date_from'])->toDateString());
        }
        if (! empty($data['incident_date_to'] ?? null)) {
            $query->whereDate('incident_date', '<=', Carbon::parse($data['incident_date_to'])->toDateString());
        }

        $columns = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());
        $columnDefs = $this->excelColumnDefinitions();
        $typeMap = config('swm_complaints.complaint_types', []);
        $throughMap = config('swm_complaints.submitted_through', []);
        $statusMap = config('swm_complaints.complaint_statuses', []);

        (new SwmExcelExportWriter())->download(SwmExcelFilename::export('complaints'), $columns, function ($sheet, $colLetter) use ($query, $columnDefs, $typeMap, $throughMap, $statusMap) {
            $rowNum = 2;
            $query->orderBy('id')->chunk(5000, function ($rows) use ($sheet, $colLetter, &$rowNum, $columnDefs, $typeMap, $throughMap, $statusMap) {
                foreach ($rows as $row) {
                    $values = SwmExcelColumns::buildExportRow(
                        $columnDefs,
                        $row,
                        fn (string $key, $model) => $this->formatComplaintExportValue($key, $model, $typeMap, $throughMap, $statusMap)
                    );
                    foreach ($values as $index => $value) {
                        $sheet->setCellValue($colLetter($index + 1).$rowNum, $value);
                    }
                    $rowNum++;
                }
            });
        });
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('complaints'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, template?: bool, derived?: bool, required?: bool, dropdown?: array<int, string>}> */
    protected function excelColumnDefinitions(): array
    {
        return [
            ['key' => 'complaint_id', 'label' => __('Complaint ID'), 'import' => false, 'template' => true, 'derived' => true],
            ['key' => 'holding_number', 'label' => __('Holding Number')],
            ['key' => 'household_id', 'label' => __('Household ID')],
            ['key' => 'name', 'label' => __('Name'), 'required' => true],
            ['key' => 'contact_number', 'label' => __('Contact Number'), 'required' => true],
            ['key' => 'ward_no', 'label' => __('Ward No.'), 'dropdown' => SwmImportTemplateOptions::wardNumberStrings()],
            ['key' => 'incident_date', 'label' => __('Incident Date')],
            ['key' => 'complaint_type', 'label' => __('Complaint Type'), 'required' => true, 'dropdown' => array_values(config('swm_complaints.complaint_types', []))],
            ['key' => 'submitted_through', 'label' => __('Complaint Submitted Through'), 'required' => true, 'dropdown' => array_values(config('swm_complaints.submitted_through', []))],
            ['key' => 'complaint_status', 'label' => __('Complaint Status'), 'required' => true, 'dropdown' => array_values(config('swm_complaints.complaint_statuses', []))],
            ['key' => 'complaint_status_other', 'label' => __('Complaint Status Other'), 'export' => false],
            ['key' => 'resolution_time_days', 'label' => __('Resolution Time (Days)')],
            ['key' => 'priority_level', 'label' => __('Priority Level (1-5)'), 'dropdown' => SwmImportTemplateOptions::priorityLevels()],
            ['key' => 'assigned_to', 'label' => __('Assigned To')],
            ['key' => 'complaint_details', 'label' => __('Complaint Details'), 'required' => true],
            ['key' => 'notes', 'label' => __('Notes')],
            ['key' => 'duplicate_complaint', 'label' => __('Duplicate Complaint'), 'dropdown' => SwmImportTemplateOptions::yesNo()],
            ['key' => 'duplicate_reference', 'label' => __('Duplicate Complaint ID')],
            ['key' => 'date_time', 'label' => __('Date and Time')],
        ];
    }

    /**
     * @param  array<string, string>  $typeMap
     * @param  array<string, string>  $throughMap
     * @param  array<string, string>  $statusMap
     */
    protected function formatComplaintExportValue(string $key, $row, array $typeMap, array $throughMap, array $statusMap): mixed
    {
        return match ($key) {
            'complaint_id' => $row->complaint_id,
            'holding_number' => $row->holding_number,
            'household_id' => $row->customer_id,
            'name' => $row->name,
            'contact_number' => $row->contact_number,
            'ward_no' => $row->ward_no,
            'incident_date' => $row->incident_date?->format('Y-m-d'),
            'complaint_type' => $typeMap[$row->complaint_type] ?? $row->complaint_type,
            'submitted_through' => $throughMap[$row->submitted_through] ?? $row->submitted_through,
            'complaint_status' => ($row->complaint_status === 'others' && ! empty($row->complaint_status_other))
                ? (($statusMap['others'] ?? __('Other')).': '.$row->complaint_status_other)
                : ($statusMap[$row->complaint_status] ?? $row->complaint_status),
            'resolution_time_days' => $row->resolution_time_days,
            'priority_level' => $row->priority_level,
            'assigned_to' => $row->assigned_to,
            'complaint_details' => $row->complaint_details,
            'notes' => $row->notes,
            'duplicate_complaint' => $row->duplicate_complaint ? __('Yes') : __('No'),
            'duplicate_reference' => $row->duplicate_reference,
            'date_time' => $row->date_time?->format('Y-m-d H:i:s'),
            default => '',
        };
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    public function importColumnDefinitions(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'photo_attachment' => __('Photo Attachment'),
        ];
    }
}
