<?php

namespace App\Services\Swm;

use App\Models\Swm\Sts;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\DataTables;

class StsService
{
    protected function baseQuery(): Builder
    {
        return Sts::query()
            ->select('swm.sts.*')
            ->leftJoin('swm.landfills as swm_lf', function ($join) {
                $join->on('swm.sts.destination_landfill_id', '=', 'swm_lf.id')
                    ->whereNull('swm_lf.deleted_at');
            })
            ->addSelect(['swm_lf.name as destination_landfill_name'])
            ->whereNull('swm.sts.deleted_at');
    }

    public function getAllSts(array $data)
    {
        $query = $this->baseQuery();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('swm.sts.name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['operator_name'] ?? null)) {
                    $q->where('swm.sts.operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('swm.sts.contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (! empty($data['destination_landfill_id'] ?? null)) {
                    $q->where('swm.sts.destination_landfill_id', $data['destination_landfill_id']);
                }
                if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
                    $q->where('swm.sts.segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
                }
            })
            ->orderColumn('destination_landfill_name', 'swm_lf.name $1')
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.sts.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW STS')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\StsController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW STS')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\StsController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW STS History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\StsController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW STS')) {
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
            $sts = new Sts();
        } else {
            $sts = Sts::find($id);
            if (! $sts) {
                return null;
            }
        }

        $sts->name = $data['name'] ?? null;
        $sts->location = $data['location'] ?? null;
        $sts->operator_name = $data['operator_name'] ?? null;
        $sts->contact_number = $data['contact_number'] ?? null;
        $sts->capacity = $data['capacity'] ?? null;
        $sts->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $sts->destination_landfill_id = $data['destination_landfill_id'] ?? null;
        $sts->save();

        return $sts->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('Name'),
            __('Location'),
            __('Operator Name'),
            __('Contact Number'),
            __('Capacity'),
            __('Segregation Practiced'),
            __('Destination Landfill'),
        ];

        $query = $this->baseQuery();

        if (! empty($data['name'] ?? null)) {
            $query->where('swm.sts.name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['operator_name'] ?? null)) {
            $query->where('swm.sts.operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('swm.sts.contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['destination_landfill_id'] ?? null)) {
            $query->where('swm.sts.destination_landfill_id', $data['destination_landfill_id']);
        }
        if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
            $query->where('swm.sts.segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW STS.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('swm.sts.id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->location,
                    $row->operator_name,
                    $row->contact_number,
                    $row->capacity,
                    $row->segregation_practiced ? __('Yes') : __('No'),
                    $row->destination_landfill_name,
                ]);
            }
        });

        $writer->close();
    }
}
