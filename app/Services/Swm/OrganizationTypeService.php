<?php

namespace App\Services\Swm;

use App\Models\Swm\OrganizationType;
use App\Support\Swm\SwmExcelFilename;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class OrganizationTypeService
{
    public function getAllOrganizationTypes(array $data)
    {
        $query = OrganizationType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.organization-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Organization Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\OrganizationTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Organization Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\OrganizationTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Organization Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\OrganizationTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Organization Type')) {
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
            $organizationType = new OrganizationType();
        } else {
            $organizationType = OrganizationType::find($id);
            if (! $organizationType) {
                return null;
            }
        }

        $organizationType->name = $data['name'] ?? null;
        $organizationType->description = $data['description'] ?? null;
        $organizationType->save();

        return $organizationType->id;
    }

    public function download(array $data): void
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Organization Type'),
            __('Description'),
        ];

        $query = OrganizationType::query()
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
        $writer->openToBrowser(SwmExcelFilename::export('organization_types'))
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
            'name' => __('Organization Type'),
            'description' => __('Description'),
        ];
    }
}
