<?php

namespace App\Services\Swm;

use App\Models\Swm\Lic;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class LicService
{
    public function getAllLic(array $data)
    {
        $query = Lic::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['lic_id'] ?? null)) {
                    $q->where('lic_id', 'ILIKE', '%'.trim((string) $data['lic_id']).'%');
                }
                if (! empty($data['representative_name'] ?? null)) {
                    $q->where('representative_name', 'ILIKE', '%'.trim((string) $data['representative_name']).'%');
                }
                if (! empty($data['contact_no'] ?? null)) {
                    $q->where('contact_no', 'ILIKE', '%'.trim((string) $data['contact_no']).'%');
                }
                if (! empty($data['number_of_hhs'] ?? null)) {
                    $q->where('number_of_hhs', $data['number_of_hhs']);
                }
                if (! empty($data['total_population'] ?? null)) {
                    $q->where('total_population', $data['total_population']);
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.lic.destroy', $model->id]]);

                if (Auth::user()->can('Edit SWM LIC')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\LicController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SWM LIC')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\LicController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SWM LIC History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\LicController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SWM LIC')) {
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
            $lic = new Lic();
        } else {
            $lic = Lic::find($id);
            if (! $lic) {
                return null;
            }
        }

        $lic->lic_id = $data['lic_id'] ?? null;
        $lic->representative_name = $data['representative_name'] ?? null;
        $lic->contact_no = $data['contact_no'] ?? null;
        $lic->number_of_hhs = $data['number_of_hhs'] ?? null;
        $lic->total_population = $data['total_population'] ?? null;
        $lic->save();

        return $lic->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('LIC ID'),
            __("LIC Representative's Name"),
            __('Contact No.'),
            __('Number of HHs'),
            __('Total Population'),
        ];

        $query = Lic::query()->whereNull('deleted_at');

        if (! empty($data['lic_id'] ?? null)) {
            $query->where('lic_id', 'ILIKE', '%'.trim((string) $data['lic_id']).'%');
        }
        if (! empty($data['representative_name'] ?? null)) {
            $query->where('representative_name', 'ILIKE', '%'.trim((string) $data['representative_name']).'%');
        }
        if (! empty($data['contact_no'] ?? null)) {
            $query->where('contact_no', 'ILIKE', '%'.trim((string) $data['contact_no']).'%');
        }
        if (! empty($data['number_of_hhs'] ?? null)) {
            $query->where('number_of_hhs', $data['number_of_hhs']);
        }
        if (! empty($data['total_population'] ?? null)) {
            $query->where('total_population', $data['total_population']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SWM LIC.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->lic_id,
                    $row->representative_name,
                    $row->contact_no,
                    $row->number_of_hhs,
                    $row->total_population,
                ]);
            }
        });

        $writer->close();
    }
}
