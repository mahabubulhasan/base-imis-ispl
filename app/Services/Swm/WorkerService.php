<?php

namespace App\Services\Swm;

use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
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
        $worker->email = $data['email'] ?? null;
        $worker->age = $data['age'] ?? null;
        $worker->gender = $data['gender'] ?? null;
        $worker->service_area = $data['service_area'] ?? null;
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
                if (! empty($data['email'] ?? null)) {
                    $q->where('swm.workers.email', 'ILIKE', '%'.trim((string) $data['email']).'%');
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

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;
        $mobile = $data['mobile'] ?? null;
        $email = $data['email'] ?? null;
        $workerIdNo = $data['worker_id_no'] ?? null;
        $employeeId = $data['employee_id'] ?? null;
        $nationalIdNo = $data['national_id_no'] ?? null;
        $employmentType = $data['employment_type'] ?? null;
        $status = $data['status'] ?? null;
        $organizationId = $data['organization_id'] ?? null;
        $workTypeId = $data['work_type_id'] ?? null;

        $columns = [
            __('Worker Name'),
            __('Worker ID'),
            __('Organization'),
            __('Work Type'),
            __('Mobile'),
            __('Email'),
            __('Employee ID'),
            __('National ID'),
            __('Employment Type'),
            __('Status'),
        ];

        $query = $this->baseQuery();

        if (! empty($name)) {
            $query->where('swm.workers.name', 'ILIKE', '%'.trim((string) $name).'%');
        }
        if (! empty($mobile)) {
            $query->where('swm.workers.mobile', 'ILIKE', '%'.trim((string) $mobile).'%');
        }
        if (! empty($email)) {
            $query->where('swm.workers.email', 'ILIKE', '%'.trim((string) $email).'%');
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

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Workers.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('swm.workers.id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->worker_id_no,
                    $row->organization_name,
                    $row->work_type_name,
                    $row->mobile,
                    $row->email,
                    $row->employee_id,
                    $row->national_id_no,
                    $row->employment_type,
                    $row->status,
                ]);
            }
        });

        $writer->close();
    }
}
