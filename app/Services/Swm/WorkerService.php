<?php

namespace App\Services\Swm;

use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Models\Swm\WorkType;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\ExcelDownload;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class WorkerService
{
    use HasExcelColumnValidationLabels;

    protected function baseQuery(): Builder
    {
        $query = Worker::query()
            ->select('swm.workers.*')
            ->leftJoin('swm.organizations as swm_org', 'swm.workers.organization_id', '=', 'swm_org.id')
            ->leftJoin('swm.work_types as swm_wt', 'swm.workers.work_type_id', '=', 'swm_wt.id')
            ->addSelect([
                'swm_org.name as organization_name',
                'swm_wt.name as work_type_name',
            ])
            ->whereNull('swm.workers.deleted_at');

        $orgId = Auth::user()?->swm_organization_id;
        if ($orgId) {
            $query->where('swm.workers.organization_id', $orgId);
        }

        return $query;
    }

    /**
     * Next Worker ID for display (preview). Call allocate inside a transaction with org locked before insert.
     */
    public function peekNextWorkerIdNo(int $organizationId): string
    {
        $width = max(1, (int) config('swm.worker_id.sequence_width', 5));
        $nextSeq = $this->maxSequenceForOrganization($organizationId) + 1;

        return $this->workerIdPrefix($organizationId).str_pad((string) $nextSeq, $width, '0', STR_PAD_LEFT);
    }

    protected function workerIdPrefix(int $organizationId): string
    {
        $format = (string) config('swm.worker_id.prefix_format', 'WKR-%d-');

        return sprintf($format, $organizationId);
    }

    protected function maxSequenceForOrganization(int $organizationId): int
    {
        $prefix = $this->workerIdPrefix($organizationId);
        $regex = '^'.preg_quote($prefix, '/').'[0-9]+$';

        $maxSeq = Worker::query()
            ->where('organization_id', $organizationId)
            ->whereNull('deleted_at')
            ->whereRaw('worker_id_no ~ ?', [$regex])
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(worker_id_no FROM '[0-9]+$') AS INTEGER)), 0) AS max_seq")
            ->value('max_seq');

        return (int) $maxSeq;
    }

    protected function workerIdNoIsMissing(?string $workerIdNo): bool
    {
        return $workerIdNo === null || $workerIdNo === '';
    }

    protected function fillWorkerFromData(Worker $worker, array $data): void
    {
        $worker->organization_id = $data['organization_id'] ?? null;
        $worker->work_type_id = $data['work_type_id'] ?? null;
        $worker->name = $data['name'] ?? null;
        $worker->mobile = $data['mobile'] ?? null;
        $worker->age = $data['age'] ?? null;
        $worker->gender = $data['gender'] ?? null;
        if (array_key_exists('service_area', $data)) {
            $worker->service_area = $data['service_area'];
        }
        $worker->service_wards = $data['service_wards'] ?? null;
        $worker->employment_type = $data['employment_type'] ?? null;
        $worker->status = $data['status'] ?? 'active';
        $worker->department = $data['department'] ?? null;
        $worker->supervisor_name = $data['supervisor_name'] ?? null;
        $worker->total_work_experience_years = $data['total_work_experience_years'] ?? null;
        $worker->organization_work_experience_years = $data['organization_work_experience_years'] ?? null;
        $worker->education_level = $data['education_level'] ?? null;
        $worker->education_level_other = $data['education_level_other'] ?? null;
        $worker->employee_id = $data['employee_id'] ?? null;
        $worker->national_id_no = $data['national_id_no'] ?? null;
    }

    public function getAllWorkers(array $data)
    {
        $query = $this->baseQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('swm.workers.name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['mobile'] ?? null)) {
                    $q->where('swm.workers.mobile', 'ILIKE', '%'.trim((string) $data['mobile']).'%');
                }
                if (! empty($data['worker_id_no'] ?? null)) {
                    $q->where('swm.workers.worker_id_no', 'ILIKE', '%'.trim((string) $data['worker_id_no']).'%');
                }
                if (! empty($data['employee_id'] ?? null)) {
                    $q->where('swm.workers.employee_id', 'ILIKE', '%'.trim((string) $data['employee_id']).'%');
                }
                if (! empty($data['national_id_no'] ?? null)) {
                    $q->where('swm.workers.national_id_no', 'ILIKE', '%'.trim((string) $data['national_id_no']).'%');
                }
                if (! empty($data['employment_type'] ?? null)) {
                    $q->where('swm.workers.employment_type', $data['employment_type']);
                }
                if (! empty($data['status'] ?? null)) {
                    $q->where('swm.workers.status', $data['status']);
                }
                if (! empty($data['organization_id'] ?? null)) {
                    $q->where('swm.workers.organization_id', $data['organization_id']);
                }
                if (! empty($data['work_type_id'] ?? null)) {
                    $q->where('swm.workers.work_type_id', $data['work_type_id']);
                }
            })
            ->orderColumn('organization_name', 'swm_org.name $1')
            ->orderColumn('work_type_name', 'swm_wt.name $1')
            ->orderColumn('worker_id_no', 'swm.workers.worker_id_no $1')
            ->editColumn('worker_id_no', fn ($model) => $model->getAttribute('worker_id_no'))
            ->editColumn('employment_type', fn ($model) => Worker::employmentTypeLabel($model->employment_type))
            ->editColumn('status', fn ($model) => Worker::statusLabel($model->status))
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.workers.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Worker')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WorkerController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Worker')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WorkerController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Worker History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WorkerController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Worker')) {
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
        unset($data['worker_id_no']);

        if (is_null($id)) {
            return DB::transaction(function () use ($data) {
                $orgId = (int) ($data['organization_id'] ?? 0);
                Organization::query()->whereKey($orgId)->lockForUpdate()->first();

                $worker = new Worker();
                $this->fillWorkerFromData($worker, $data);
                $worker->worker_id_no = $this->peekNextWorkerIdNo($orgId);
                $worker->save();

                return $worker->id;
            });
        }

        $worker = Worker::find($id);
        if (! $worker) {
            return null;
        }

        return DB::transaction(function () use ($worker, $data) {
            $this->fillWorkerFromData($worker, $data);

            $orgId = (int) ($worker->organization_id ?? 0);
            if ($orgId > 0) {
                Organization::query()->whereKey($orgId)->lockForUpdate()->first();
            }

            if ($orgId > 0 && $this->workerIdNoIsMissing($worker->worker_id_no)) {
                $worker->worker_id_no = $this->peekNextWorkerIdNo($orgId);
            }

            $worker->save();

            return $worker->id;
        });
    }

    public function download(array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $name = $data['name'] ?? null;
        $mobile = $data['mobile'] ?? null;
        $workerIdNo = $data['worker_id_no'] ?? null;
        $employeeId = $data['employee_id'] ?? null;
        $nationalIdNo = $data['national_id_no'] ?? null;
        $employmentType = $data['employment_type'] ?? null;
        $status = $data['status'] ?? null;
        $organizationId = $data['organization_id'] ?? null;
        $workTypeId = $data['work_type_id'] ?? null;

        $columns = SwmExcelColumns::exportHeaders($this->excelColumnDefinitions());

        $query = $this->baseQuery();

        if (! empty($name)) {
            $query->where('swm.workers.name', 'ILIKE', '%'.trim((string) $name).'%');
        }
        if (! empty($mobile)) {
            $query->where('swm.workers.mobile', 'ILIKE', '%'.trim((string) $mobile).'%');
        }
        if (! empty($workerIdNo)) {
            $query->where('swm.workers.worker_id_no', 'ILIKE', '%'.trim((string) $workerIdNo).'%');
        }
        if (! empty($employeeId)) {
            $query->where('swm.workers.employee_id', 'ILIKE', '%'.trim((string) $employeeId).'%');
        }
        if (! empty($nationalIdNo)) {
            $query->where('swm.workers.national_id_no', 'ILIKE', '%'.trim((string) $nationalIdNo).'%');
        }
        if (! empty($employmentType)) {
            $query->where('swm.workers.employment_type', $employmentType);
        }
        if (! empty($status)) {
            $query->where('swm.workers.status', $status);
        }
        if (! empty($organizationId)) {
            $query->where('swm.workers.organization_id', $organizationId);
        }
        if (! empty($workTypeId)) {
            $query->where('swm.workers.work_type_id', $workTypeId);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        return ExcelDownload::xlsx(
            SwmExcelFilename::export('workers'),
            function ($writer) use ($columns, $style, $query) {
                $writer->addRowWithStyle($columns, $style);

                $query->orderBy('swm.workers.id')->chunk(5000, function ($rows) use ($writer, $columns) {
                    $columnDefinitions = $this->excelColumnDefinitions();
                    foreach ($rows as $row) {
                        $writer->addRow(SwmExcelColumns::buildExportRow($columnDefinitions, $row, fn (string $key, $model) => $this->formatWorkerExportValue($key, $model)));
                    }
                });
            }
        );
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('workers'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool, import?: bool, export?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function excelColumnDefinitions(): array
    {
        $scopedOrgId = Auth::user()?->swm_organization_id;
        $columns = [
            ['key' => 'worker_id_no', 'label' => __('ID'), 'import' => false, 'template' => false, 'derived' => true],
            ['key' => 'name', 'label' => __('Name'), 'required' => true],
            ['key' => 'age', 'label' => __('Age (Years)')],
            ['key' => 'gender', 'label' => __('Gender'), 'dropdown' => [__('Male'), __('Female'), __('Others')]],
            ['key' => 'mobile', 'label' => __('Contact'), 'required' => true],
            ['key' => 'department', 'label' => __('Department')],
            ['key' => 'work_type', 'label' => __('Worker Type'), 'required' => true, 'dropdown' => WorkType::query()
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->pluck('name')
                ->all(), 'export' => false],
            ['key' => 'work_type_name', 'label' => __('Worker Type'), 'import' => false],
            [
                'key' => 'service_wards',
                'label' => __('Service Wards'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wardNumberStrings(),
                'reference_key' => 'service_wards',
            ],
            ['key' => 'employment_type', 'label' => __('Employment Type'), 'dropdown' => [__('Permanent'), __('Daily'), __('Contract')]],
        ];

        if (! $scopedOrgId) {
            $columns[] = ['key' => 'organization', 'label' => __('Organization'), 'required' => true, 'dropdown' => Organization::query()
                ->whereNull('deleted_at')
                ->operational()
                ->orderBy('name')
                ->pluck('name')
                ->all(), 'export' => false];
            $columns[] = ['key' => 'organization_name', 'label' => __('Organization'), 'import' => false];
        } else {
            $columns[] = ['key' => 'organization_name', 'label' => __('Organization'), 'import' => false, 'template' => true, 'derived' => true];
        }

        $columns = array_merge($columns, [
            ['key' => 'supervisor_name', 'label' => __('Supervisor\'s Name')],
            ['key' => 'total_work_experience_years', 'label' => __('Total Work Experience (Years)')],
            ['key' => 'organization_work_experience_years', 'label' => __('Work Experience in This Organization (Years)')],
            ['key' => 'education_level', 'label' => __('Education Level'), 'dropdown' => [
                __('Primary'),
                __('Secondary (Below SSC)'),
                __('SSC'),
                __('HSC'),
                __('Bachelor'),
                __('Master'),
                __('Others (specify)'),
            ]],
            ['key' => 'education_level_other', 'label' => __('Education (Other)')],
            ['key' => 'employee_id', 'label' => __('Employee ID (Current Organization)')],
            ['key' => 'national_id_no', 'label' => __('National ID')],
            ['key' => 'status', 'label' => __('Status'), 'dropdown' => [__('Active'), __('Inactive')]]
        ]);

        return $columns;
    }

    protected function formatWorkerExportValue(string $key, $row): mixed
    {
        return match ($key) {
            'worker_id_no' => $row->worker_id_no,
            'name' => $row->name,
            'age' => $row->age,
            'gender' => $row->gender ? __(ucfirst($row->gender)) : '',
            'mobile' => $row->mobile,
            'department' => $row->department,
            'work_type_name' => $row->work_type_name,
            'service_wards' => is_array($row->service_wards) ? implode(', ', $row->service_wards) : ($row->service_wards ?? ''),
            'employment_type' => Worker::employmentTypeLabel($row->employment_type),
            'organization_name' => $row->organization_name,
            'supervisor_name' => $row->supervisor_name,
            'total_work_experience_years' => $row->total_work_experience_years,
            'organization_work_experience_years' => $row->organization_work_experience_years,
            'education_level' => $row->education_level ? __(ucfirst($row->education_level)) : '',
            'education_level_other' => $row->education_level_other,
            'employee_id' => $row->employee_id,
            'national_id_no' => $row->national_id_no,
            'status' => Worker::statusLabel($row->status),
            default => '',
        };
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'organization_id' => __('Organization'),
            'work_type_id' => __('Worker Type'),
        ];
    }
}
