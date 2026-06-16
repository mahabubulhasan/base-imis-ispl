<?php

namespace App\Services\Swm;

use App\Models\Swm\AttendanceLog;
use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class AttendanceLogService
{
    public function attendanceQuery(): Builder
    {
        $query = AttendanceLog::query()
            ->whereNull('deleted_at')
            ->with(['organization', 'worker']);

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where('organization_id', (int) $orgId);
        }

        return $query;
    }

    public function getAllAttendanceLogs(array $data)
    {
        $query = $this->attendanceQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['organization_id'] ?? null)) {
                    $q->where('organization_id', (int) $data['organization_id']);
                }
                if (! empty($data['worker_search'] ?? null)) {
                    $term = '%'.trim((string) $data['worker_search']).'%';
                    $q->whereHas('worker', function ($wq) use ($term) {
                        $wq->where('name', 'ILIKE', $term)
                            ->orWhere('worker_id_no', 'ILIKE', $term);
                    });
                }
                if (! empty($data['attendance_status'] ?? null)) {
                    $q->where('attendance_status', $data['attendance_status']);
                }
                if (! empty($data['date_from'] ?? null)) {
                    $q->whereDate('entry_at', '>=', Carbon::parse($data['date_from'])->toDateString());
                }
                if (! empty($data['date_to'] ?? null)) {
                    $q->whereDate('entry_at', '<=', Carbon::parse($data['date_to'])->toDateString());
                }
            })
            ->addColumn('organization_name', function (AttendanceLog $model) {
                return $model->organization?->name ?? '';
            })
            ->addColumn('worker_label', function (AttendanceLog $model) {
                $w = $model->worker;
                if (! $w) {
                    return '';
                }
                $no = $w->worker_id_no ? ' — '.$w->worker_id_no : '';

                return $w->name.$no;
            })
            ->editColumn('entry_at', function (AttendanceLog $model) {
                return $model->entry_at?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('check_in_at', function (AttendanceLog $model) {
                return $model->check_in_at?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('check_out_at', function (AttendanceLog $model) {
                return $model->check_out_at?->format('Y-m-d H:i') ?? '';
            })
            ->editColumn('attendance_status', function (AttendanceLog $model) {
                return AttendanceLog::statusOptions()[$model->attendance_status] ?? $model->attendance_status;
            })
            ->orderColumn('organization_name', function ($query, $order) {
                $query->orderBy('organization_id', $order);
            })
            ->addColumn('action', function (AttendanceLog $model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.attendance-logs.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Attendance Log')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\AttendanceLogController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Attendance Log')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\AttendanceLogController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Attendance Log History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\AttendanceLogController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Attendance Log')) {
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
        $worker = Worker::query()
            ->whereNull('deleted_at')
            ->whereKey($data['worker_id'])
            ->where('organization_id', $data['organization_id'])
            ->with('workType')
            ->first();

        if (! $worker) {
            return null;
        }

        if (is_null($id)) {
            $log = new AttendanceLog();
        } else {
            $log = AttendanceLog::query()->find($id);
            if (! $log) {
                return null;
            }
        }

        DB::transaction(function () use ($log, $data, $worker): void {
            $log->organization_id = (int) $data['organization_id'];
            $log->worker_id = (int) $data['worker_id'];
            $log->department = $data['department'] ?? null;
            $log->entry_at = Carbon::parse($data['entry_at']);
            $log->attendance_status = $data['attendance_status'];
            $log->remarks = $data['remarks'] ?? null;

            $log->work_type_id = $worker->work_type_id;
            $log->work_type_name = $worker->workType?->name;
            $log->supervisor_name = $worker->supervisor_name;

            if ($data['attendance_status'] === AttendanceLog::STATUS_PRESENT) {
                $log->check_in_at = ! empty($data['check_in_at'] ?? null)
                    ? Carbon::parse($data['check_in_at'])
                    : null;
                $log->check_out_at = ! empty($data['check_out_at'] ?? null)
                    ? Carbon::parse($data['check_out_at'])
                    : null;
            } else {
                $log->check_in_at = null;
                $log->check_out_at = null;
            }

            $log->save();
        });

        return $log->id;
    }

    public function download(array $data): void
    {
        $query = $this->attendanceQuery();

        if (! empty($data['organization_id'] ?? null)) {
            $query->where('organization_id', (int) $data['organization_id']);
        }
        if (! empty($data['worker_search'] ?? null)) {
            $term = '%'.trim((string) $data['worker_search']).'%';
            $query->whereHas('worker', function ($wq) use ($term) {
                $wq->where('name', 'ILIKE', $term)
                    ->orWhere('worker_id_no', 'ILIKE', $term);
            });
        }
        if (! empty($data['attendance_status'] ?? null)) {
            $query->where('attendance_status', $data['attendance_status']);
        }
        if (! empty($data['date_from'] ?? null)) {
            $query->whereDate('entry_at', '>=', Carbon::parse($data['date_from'])->toDateString());
        }
        if (! empty($data['date_to'] ?? null)) {
            $query->whereDate('entry_at', '<=', Carbon::parse($data['date_to'])->toDateString());
        }

        $columns = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser(SwmExcelFilename::export('attendance_logs'))
            ->addRowWithStyle($columns, $style);

        $statusLabels = AttendanceLog::statusOptions();

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $statusLabels) {
            foreach ($rows as $row) {
                $worker = $row->worker;
                $workerLabel = $worker ? ($worker->name.($worker->worker_id_no ? ' — '.$worker->worker_id_no : '')) : '';

                $writer->addRow([
                    $row->id,
                    $row->entry_at?->format('Y-m-d H:i:s'),
                    $row->organization?->name,
                    $row->department,
                    $workerLabel,
                    $row->work_type_name,
                    $row->supervisor_name,
                    $statusLabels[$row->attendance_status] ?? $row->attendance_status,
                    $row->check_in_at?->format('Y-m-d H:i:s'),
                    $row->check_out_at?->format('Y-m-d H:i:s'),
                    $row->remarks,
                ]);
            }
        });

        $writer->close();
    }

    public function downloadTemplate(): void
    {
        app(SwmExcelTemplateWriter::class)->download(
            SwmExcelFilename::importTemplate('attendance_logs'),
            SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>}> */
    protected function excelColumnDefinitions(): array
    {
        $columns = [];
        $orgId = Auth::user()?->swm_organization_id;

        if (! $orgId) {
            $columns[] = [
                'key' => 'organization',
                'label' => __('Organization'),
                'export' => false,
                'required' => true,
                'dropdown' => array_values(Organization::query()
                    ->whereNull('deleted_at')
                    ->operational()
                    ->orderBy('name')
                    ->pluck('name')
                    ->all()),
            ];
        }

        $workerQuery = Worker::query()->whereNull('deleted_at');
        if ($orgId) {
            $workerQuery->where('organization_id', (int) $orgId);
        }
        $workerLabels = $workerQuery->orderBy('name')->get(['name', 'worker_id_no'])
            ->map(fn (Worker $w) => $w->name.($w->worker_id_no ? ' — '.$w->worker_id_no : ''))
            ->values()
            ->all();

        $statusLabels = array_values(AttendanceLog::statusOptions());

        return array_merge($columns, [
            ['key' => 'id', 'label' => __('ID'), 'import' => false],
            ['key' => 'entry_at', 'label' => __('Entry Date and Time'), 'required' => true],
            ['key' => 'organization_name', 'label' => __('Organization'), 'import' => false],
            ['key' => 'department', 'label' => __('Department')],
            ['key' => 'worker', 'label' => __('Worker Name-ID'), 'required' => true, 'dropdown' => $workerLabels],
            ['key' => 'work_type_name', 'label' => __('Worker Type'), 'import' => false],
            ['key' => 'supervisor_name', 'label' => __('Supervisor Name'), 'import' => false],
            ['key' => 'attendance_status', 'label' => __('Attendance Status'), 'required' => true, 'dropdown' => $statusLabels],
            ['key' => 'check_in_at', 'label' => __('Check-in Time')],
            ['key' => 'check_out_at', 'label' => __('Check-out Time')],
            ['key' => 'remarks', 'label' => __('Remarks')],
        ]);
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    protected function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }
}
