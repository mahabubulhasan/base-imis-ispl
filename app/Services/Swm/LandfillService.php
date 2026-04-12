<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class LandfillService
{
    public function getAllLandfills(array $data)
    {
        $query = Landfill::query();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['operator_name'] ?? null)) {
                    $q->where('operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
                    $q->where('segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
                }
                if (array_key_exists('reuse_practiced', $data) && $data['reuse_practiced'] !== '' && $data['reuse_practiced'] !== null) {
                    $q->where('reuse_practiced', filter_var($data['reuse_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['reuse_practiced']);
                }
                if (array_key_exists('treatment', $data) && $data['treatment'] !== '' && $data['treatment'] !== null) {
                    $q->where('treatment', filter_var($data['treatment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['treatment']);
                }
            })
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
            ->editColumn('reuse_practiced', fn ($m) => $m->reuse_practiced ? __('Yes') : __('No'))
            ->editColumn('treatment', fn ($m) => $m->treatment ? __('Yes') : __('No'))
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.landfills.destroy', $model->id]]);

                if (Auth::user()->can('Edit SWM Landfill')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\LandfillController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SWM Landfill')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\LandfillController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SWM Landfill History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\LandfillController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SWM Landfill')) {
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
            $landfill = new Landfill();
        } else {
            $landfill = Landfill::find($id);
            if (! $landfill) {
                return null;
            }
        }

        $landfill->name = $data['name'] ?? null;
        $landfill->location = $data['location'] ?? null;
        $landfill->operator_name = $data['operator_name'] ?? null;
        $landfill->contact_number = $data['contact_number'] ?? null;
        $landfill->capacity = $data['capacity'] ?? null;
        $landfill->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $landfill->reuse_practiced = (bool) ($data['reuse_practiced'] ?? false);
        $landfill->monthly_waste_for_composting = $data['monthly_waste_for_composting'] ?? null;
        $landfill->treatment = (bool) ($data['treatment'] ?? false);
        $landfill->save();

        return $landfill->id;
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
            __('Reuse Practiced'),
            __('Monthly Waste for Composting'),
            __('Treatment'),
        ];

        $query = Landfill::query()->whereNull('deleted_at');

        if (! empty($data['name'] ?? null)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['operator_name'] ?? null)) {
            $query->where('operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
            $query->where('segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
        }
        if (array_key_exists('reuse_practiced', $data) && $data['reuse_practiced'] !== '' && $data['reuse_practiced'] !== null) {
            $query->where('reuse_practiced', filter_var($data['reuse_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['reuse_practiced']);
        }
        if (array_key_exists('treatment', $data) && $data['treatment'] !== '' && $data['treatment'] !== null) {
            $query->where('treatment', filter_var($data['treatment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['treatment']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SWM Landfills.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->location,
                    $row->operator_name,
                    $row->contact_number,
                    $row->capacity,
                    $row->segregation_practiced ? __('Yes') : __('No'),
                    $row->reuse_practiced ? __('Yes') : __('No'),
                    $row->monthly_waste_for_composting,
                    $row->treatment ? __('Yes') : __('No'),
                ]);
            }
        });

        $writer->close();
    }
}
