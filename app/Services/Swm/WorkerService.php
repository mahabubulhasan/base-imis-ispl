<?php

namespace App\Services\Swm;

use App\Models\Swm\Worker;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Database\Eloquent\Builder;
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
                if (! empty($data['organization_id'] ?? null)) {
                    $q->where('swm.workers.organization_id', $data['organization_id']);
                }
                if (! empty($data['work_type_id'] ?? null)) {
                    $q->where('swm.workers.work_type_id', $data['work_type_id']);
                }
            })
            ->orderColumn('organization_name', 'swm_org.name $1')
            ->orderColumn('work_type_name', 'swm_wt.name $1')
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.workers.destroy', $model->id]]);

                if (Auth::user()->can('Edit SWM Worker')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WorkerController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SWM Worker')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WorkerController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SWM Worker History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WorkerController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SWM Worker')) {
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
            $worker = new Worker();
        } else {
            $worker = Worker::find($id);
            if (! $worker) {
                return null;
            }
        }

        $worker->organization_id = $data['organization_id'] ?? null;
        $worker->work_type_id = $data['work_type_id'] ?? null;
        $worker->name = $data['name'] ?? null;
        $worker->mobile = $data['mobile'] ?? null;
        $worker->email = $data['email'] ?? null;
        $worker->save();

        return $worker->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;
        $mobile = $data['mobile'] ?? null;
        $email = $data['email'] ?? null;
        $organizationId = $data['organization_id'] ?? null;
        $workTypeId = $data['work_type_id'] ?? null;

        $columns = [
            __('Worker Name'),
            __('Organization'),
            __('Work Type'),
            __('Mobile'),
            __('Email'),
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
        $writer->openToBrowser('SWM Workers.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('swm.workers.id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->organization_name,
                    $row->work_type_name,
                    $row->mobile,
                    $row->email,
                ]);
            }
        });

        $writer->close();
    }
}
