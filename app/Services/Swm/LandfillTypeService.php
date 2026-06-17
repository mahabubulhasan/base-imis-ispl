<?php

namespace App\Services\Swm;

use App\Models\Swm\LandfillType;
use App\Support\Swm\SwmExcelFilename;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class LandfillTypeService
{
    public function getAllLandfillTypes(array $data)
    {
        $query = LandfillType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.landfill-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Landfill Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\LandfillTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\LandfillTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\LandfillTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Landfill Type')) {
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
            $landfillType = new LandfillType();
        } else {
            $landfillType = LandfillType::find($id);
            if (! $landfillType) {
                return null;
            }
        }

        $landfillType->name = $data['name'] ?? null;
        $landfillType->description = $data['description'] ?? null;
        $landfillType->save();

        return $landfillType->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Landfill Type'),
            __('Description'),
        ];

        $query = LandfillType::query()
            ->select('name', 'description')
            ->whereNull('deleted_at');

        if (! empty($name)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $name).'%');
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::XLSX);
        $writer->openToBrowser(SwmExcelFilename::export('landfill_types'))
            ->addRowWithStyle($columns, $style);

        $query->chunk(5000, function ($rows) use ($writer) {
            foreach ($rows as $row) {
                $writer->addRow([
                    $row->name,
                    $row->description,
                ]);
            }
        });

        $writer->close();
    }

    /** @return array<string, string> */
    public function validationAttributeLabels(): array
    {
        return [
            'name' => __('Landfill Type'),
            'description' => __('Description'),
        ];
    }
}
