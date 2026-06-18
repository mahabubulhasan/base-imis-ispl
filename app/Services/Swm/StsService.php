<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\DataTables;

class StsService
{
    use HasExcelColumnValidationLabels;

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

        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();

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
                if (! empty($data['sts_id'] ?? null)) {
                    $q->where('swm.sts.sts_id', 'ILIKE', '%'.trim((string) $data['sts_id']).'%');
                }
                if (! empty($data['ward_no'] ?? null)) {
                    $q->where('swm.sts.ward_no', (int) $data['ward_no']);
                }
                if (! empty($data['destination_landfill_id'] ?? null)) {
                    $q->where('swm.sts.destination_landfill_id', $data['destination_landfill_id']);
                }
                if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
                    $q->where('swm.sts.segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
                }
                if (! empty($data['operational_status'] ?? null)) {
                    $q->where('swm.sts.operational_status', $data['operational_status']);
                }
                if (! empty($data['waste_type_id'] ?? null)) {
                    $q->whereJsonContains('swm.sts.waste_type_ids', (int) $data['waste_type_id']);
                }
            })
            ->orderColumn('destination_landfill_name', 'swm_lf.name $1')
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
            ->editColumn('operational_status', fn ($m) => ucfirst((string) $m->operational_status))
            ->addColumn('waste_types', function ($m) use ($wasteTypeMap) {
                $ids = $m->waste_type_ids ?? [];
                if (empty($ids)) {
                    return '';
                }
                $names = [];
                foreach ($ids as $id) {
                    if (isset($wasteTypeMap[$id])) {
                        $names[] = $wasteTypeMap[$id];
                    }
                }

                return implode(', ', $names);
            })
            ->addColumn('source_wards_text', function ($m) {
                $wards = $m->source_wards ?? [];

                return empty($wards) ? '' : implode(', ', $wards);
            })
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
        $sts->ward_no = $data['ward_no'] ?? null;
        $sts->road_id = $data['road_id'] ?? null;
        $sts->road_name = $data['road_name'] ?? null;
        $sts->latitude = $data['latitude'] ?? null;
        $sts->longitude = $data['longitude'] ?? null;
        $sts->operator_name = $data['operator_name'] ?? null;
        $sts->contact_number = $data['contact_number'] ?? null;
        $sts->capacity = $data['capacity'] ?? null;
        $sts->area = $data['area'] ?? null;
        $sts->source_wards = $data['source_wards'] ?? null;
        $sts->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $sts->waste_type_ids = $data['waste_type_ids'] ?? null;
        $sts->destination_landfill_id = $data['destination_landfill_id'] ?? null;
        $sts->operational_status = $data['operational_status'] ?? 'active';

        if (! is_null($id) && empty($sts->sts_id) && ! empty($sts->ward_no)) {
            $serial = Sts::getNextSerialForWard((int) $sts->ward_no);
            $sts->sts_id = Sts::generateStsId((int) $sts->ward_no, $serial);
        }

        $sts->save();

        return $sts->id;
    }

    public function download(array $data): void
    {
        $columnDefs = $this->excelColumnDefinitions();
        $headers = SwmExcelColumns::exportHeaders($columnDefs);

        $query = $this->baseQuery();
        $this->applyExportFilters($query, $data);

        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $rows = [];

        $query->orderBy('swm.sts.id')->chunk(5000, function ($chunk) use (&$rows, $columnDefs, $wasteTypeMap) {
            foreach ($chunk as $row) {
                $rows[] = SwmExcelColumns::buildExportRow(
                    $columnDefs,
                    $row,
                    fn (string $key, $model) => $this->formatStsExportValue($key, $model, $wasteTypeMap)
                );
            }
        });

        (new SwmExcelTemplateWriter())->downloadData(SwmExcelFilename::export('sts'), $headers, $rows);
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('sts'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    protected function excelColumnDefinitions(): array
    {
        $yesNo = SwmImportTemplateOptions::yesNo();

        return [
            ['key' => 'sts_id', 'label' => __('STS ID'), 'import' => false, 'template' => false, 'derived' => true],
            ['key' => 'name', 'label' => __('STS Name'), 'required' => true],
            ['key' => 'location', 'label' => __('Location')],
            ['key' => 'ward_no', 'label' => __('Ward No.'), 'required' => true, 'dropdown' => SwmImportTemplateOptions::wardNumberStrings()],
            ['key' => 'road_id', 'label' => __('Road No.')],
            ['key' => 'road_name', 'label' => __('Road Name')],
            ['key' => 'latitude', 'label' => __('Latitude')],
            ['key' => 'longitude', 'label' => __('Longitude')],
            ['key' => 'operator_name', 'label' => __('Operator Name'), 'required' => true],
            ['key' => 'contact_number', 'label' => __("Operator's Contact Number"), 'required' => true],
            ['key' => 'capacity', 'label' => __('Capacity').' ('.__('Ton').')'],
            ['key' => 'area', 'label' => __('Area').' ('.__('Decimal').')'],
            [
                'key' => 'source_wards',
                'label' => __('Source Wards'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wardNumberStrings(),
                'reference_key' => 'source_wards',
            ],
            [
                'key' => 'waste_types',
                'label' => __('Waste Type'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::wasteTypeNames(),
                'reference_key' => 'waste_types',
            ],
            ['key' => 'segregation_practiced', 'label' => __('Segregation Practiced?'), 'dropdown' => $yesNo],
            ['key' => 'destination_landfill', 'label' => __('Destination Landfill'), 'dropdown' => SwmImportTemplateOptions::landfillLabels()],
            ['key' => 'operational_status', 'label' => __('Operational Status'), 'required' => true, 'dropdown' => ['active', 'inactive']],
        ];
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function importColumnDefinitions(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @param  array<int|string, string>  $wasteTypeMap */
    protected function formatStsExportValue(string $key, $row, array $wasteTypeMap): mixed
    {
        return match ($key) {
            'sts_id' => $row->sts_id,
            'name' => $row->name,
            'location' => $row->location,
            'ward_no' => $row->ward_no,
            'road_id' => $row->road_id,
            'road_name' => $row->road_name,
            'latitude' => $row->latitude,
            'longitude' => $row->longitude,
            'operator_name' => $row->operator_name,
            'contact_number' => $row->contact_number,
            'capacity' => $row->capacity,
            'area' => $row->area,
            'source_wards' => implode(', ', $row->source_wards ?? []),
            'waste_types' => implode(', ', array_values(array_filter(array_map(
                fn ($wid) => $wasteTypeMap[$wid] ?? null,
                $row->waste_type_ids ?? []
            )))),
            'segregation_practiced' => $row->segregation_practiced ? __('Yes') : __('No'),
            'destination_landfill' => $row->destination_landfill_name,
            'operational_status' => $row->operational_status,
            default => '',
        };
    }

    protected function applyExportFilters(Builder $query, array $data): void
    {
        if (! empty($data['name'] ?? null)) {
            $query->where('swm.sts.name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['operator_name'] ?? null)) {
            $query->where('swm.sts.operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('swm.sts.contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['sts_id'] ?? null)) {
            $query->where('swm.sts.sts_id', 'ILIKE', '%'.trim((string) $data['sts_id']).'%');
        }
        if (! empty($data['ward_no'] ?? null)) {
            $query->where('swm.sts.ward_no', (int) $data['ward_no']);
        }
        if (! empty($data['destination_landfill_id'] ?? null)) {
            $query->where('swm.sts.destination_landfill_id', $data['destination_landfill_id']);
        }
        if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
            $query->where('swm.sts.segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
        }
        if (! empty($data['operational_status'] ?? null)) {
            $query->where('swm.sts.operational_status', $data['operational_status']);
        }
        if (! empty($data['waste_type_id'] ?? null)) {
            $query->whereJsonContains('swm.sts.waste_type_ids', (int) $data['waste_type_id']);
        }
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'waste_type_ids' => __('Waste Type'),
            'destination_landfill_id' => __('Destination Landfill'),
        ];
    }
}
