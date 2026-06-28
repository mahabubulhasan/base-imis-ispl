<?php

namespace App\Services\Swm;

use App\Models\Swm\WorkType;
use App\Support\ExcelDownload;
use App\Support\Swm\SwmExcelFilename;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class WorkTypeService
{
    public function getAllWorkTypes(array $data)
    {
        $query = WorkType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.work-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Work Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\WorkTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Work Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\WorkTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Work Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\WorkTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Work Type')) {
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
            $workType = new WorkType();
        } else {
            $workType = WorkType::find($id);
            if (! $workType) {
                return null;
            }
        }

        $workType->name = $data['name'] ?? null;
        $workType->description = $data['description'] ?? null;
        $workType->save();

        return $workType->id;
    }

    public function download(array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Worker Type'),
            __('Description'),
        ];

        $query = WorkType::query()
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

        return ExcelDownload::xlsx(
            SwmExcelFilename::export('work_types'),
            function ($writer) use ($columns, $style, $query) {
                $writer->addRowWithStyle($columns, $style);

                $query->chunk(5000, function ($rows) use ($writer) {
                    foreach ($rows as $row) {
                        $writer->addRow([
                            $row->name,
                            $row->description,
                        ]);
                    }
                });
            }
        );
    }

    /** @return array<string, string> */
    public function validationAttributeLabels(): array
    {
        return [
            'name' => __('Worker Type'),
            'description' => __('Description'),
        ];
    }
}
