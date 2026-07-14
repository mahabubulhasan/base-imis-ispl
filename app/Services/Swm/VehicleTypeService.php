<?php

namespace App\Services\Swm;

use App\Models\Swm\VehicleType;
use App\Support\ExcelDownload;
use App\Support\Swm\SwmExcelFilename;
use Auth;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Style\Color;
use Box\Spout\Writer\Style\StyleBuilder;
use Box\Spout\Writer\WriterFactory;
use Yajra\DataTables\DataTables;

class VehicleTypeService
{
    public function getAllVehicleTypes(array $data)
    {
        $query = VehicleType::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
            })
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.vehicle-types.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Vehicle Type')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\VehicleTypeController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Vehicle Type')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\VehicleTypeController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Vehicle Type History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\VehicleTypeController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Vehicle Type')) {
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
            $vehicleType = new VehicleType();
        } else {
            $vehicleType = VehicleType::find($id);
            if (! $vehicleType) {
                return null;
            }
        }

        $vehicleType->name = $data['name'] ?? null;
        $vehicleType->description = $data['description'] ?? null;
        $vehicleType->save();

        return $vehicleType->id;
    }

    public function download(array $data): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $name = $data['name'] ?? null;

        $columns = [
            __('Vehicle Type'),
            __('Description'),
        ];

        $query = VehicleType::query()
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
            SwmExcelFilename::export('vehicle_types'),
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
            'name' => __('Vehicle Type'),
            'description' => __('Description'),
        ];
    }
}
