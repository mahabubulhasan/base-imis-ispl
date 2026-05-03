<?php

namespace App\Services\Swm;

use App\Models\Swm\WasteType;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class WasteTypeService
{
    public function getAllWasteTypes(array $data)
    {
        $query = WasteType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.waste-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Waste Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WasteTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WasteTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Waste Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WasteTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Waste Type')) {
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
            $wasteType = new WasteType();
        } else {
            $wasteType = WasteType::find($id);
            if (! $wasteType) {
                return null;
            }
        }

        $wasteType->name = $data['name'] ?? null;
        $wasteType->save();

        return $wasteType->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Waste Type Name'),
        ];

        $query = WasteType::query()
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
        $writer->openToBrowser('SW Waste Types.csv')
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([$row->name]);
            }
        });

        $writer->close();
    }
}
