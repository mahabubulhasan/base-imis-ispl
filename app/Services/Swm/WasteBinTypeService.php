<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteBinType;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class WasteBinTypeService
{
    public function getAllWasteBinTypes(array $data)
    {
        $query = WasteBinType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.waste-bin-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Waste Bin Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WasteBinTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Bin Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WasteBinTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Bin Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WasteBinTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Waste Bin Type')) {
                    $content .= '<a href="#" title="'.__('Delete').'" class="delete btn btn-danger btn-sm mb-1"><i class="fa fa-trash"></i></a> ';
                }

                $content .= \Form::close();

                return $content;
            })
            ->make(true);
    }

    public function storeOrUpdate(?int $id, array $data): ?int
    {
        if (is_null($id)) {
            $wasteBinType = new WasteBinType;
        } else {
            $wasteBinType = WasteBinType::find($id);
            if (! $wasteBinType) {
                return null;
            }
        }

        $wasteBinType->name = $data['name'] ?? null;
        $wasteBinType->description = $data['description'] ?? null;
        $wasteBinType->save();

        return $wasteBinType->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Waste Bin Type Name'),
        ];

        $query = WasteBinType::query()
            ->select('name')
            ->whereNull('deleted_at');

        if (! empty($name)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $name).'%');
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Waste Bin Types.csv')
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([$row->name]);
            }
        });

        $writer->close();
    }
}
