<?php

namespace App\Services\Swm;

use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Support\Swm\SwmExcelTemplateWriter;
use Auth;
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
        $headers = [
            'sts_id',
            'name',
            'location',
            'ward_no',
            'road_id',
            'road_name',
            'latitude',
            'longitude',
            'operator_name',
            'contact_number',
            'capacity',
            'area',
            'source_wards',
            'segregation_practiced',
            'waste_types',
            'destination_landfill',
            'operational_status',
        ];

        $query = $this->baseQuery();
        $this->applyExportFilters($query, $data);

        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $rows = [];

        $query->orderBy('swm.sts.id')->chunk(5000, function ($chunk) use (&$rows, $wasteTypeMap) {
            foreach ($chunk as $row) {
                $wasteIds = $row->waste_type_ids ?? [];
                $wasteNames = [];
                foreach ($wasteIds as $wid) {
                    if (isset($wasteTypeMap[$wid])) {
                        $wasteNames[] = $wasteTypeMap[$wid];
                    }
                }

                $rows[] = [
                    $row->sts_id,
                    $row->name,
                    $row->location,
                    $row->ward_no,
                    $row->road_id,
                    $row->road_name,
                    $row->latitude,
                    $row->longitude,
                    $row->operator_name,
                    $row->contact_number,
                    $row->capacity,
                    $row->area,
                    implode(', ', $row->source_wards ?? []),
                    $row->segregation_practiced ? __('Yes') : __('No'),
                    implode(', ', $wasteNames),
                    $row->destination_landfill_name,
                    $row->operational_status,
                ];
            }
        });

        (new SwmExcelTemplateWriter())->downloadData('SW STS.xlsx', $headers, $rows);
    }

    public function downloadTemplate(): void
    {
        $wards = array_map('strval', array_keys(Ward::getInAscOrder()));
        $landfills = Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Landfill $lf) => trim(($lf->landfill_id ? $lf->landfill_id.' - ' : '').$lf->name))
            ->all();

        (new SwmExcelTemplateWriter())->download('SW STS Import Template.xlsx', [
            ['key' => 'name', 'label' => 'name', 'required' => true],
            ['key' => 'location', 'label' => 'location'],
            ['key' => 'ward_no', 'label' => 'ward_no', 'required' => true, 'dropdown' => $wards],
            ['key' => 'road_id', 'label' => 'road_id'],
            ['key' => 'road_name', 'label' => 'road_name'],
            ['key' => 'latitude', 'label' => 'latitude'],
            ['key' => 'longitude', 'label' => 'longitude'],
            ['key' => 'operator_name', 'label' => 'operator_name', 'required' => true],
            ['key' => 'contact_number', 'label' => 'contact_number', 'required' => true],
            ['key' => 'capacity', 'label' => 'capacity'],
            ['key' => 'area', 'label' => 'area'],
            ['key' => 'source_wards', 'label' => 'source_wards'],
            ['key' => 'segregation_practiced', 'label' => 'segregation_practiced', 'dropdown' => [__('Yes'), __('No')]],
            ['key' => 'waste_types', 'label' => 'waste_types'],
            ['key' => 'destination_landfill', 'label' => 'destination_landfill', 'dropdown' => $landfills],
            ['key' => 'operational_status', 'label' => 'operational_status', 'required' => true, 'dropdown' => ['active', 'inactive']],
        ]);
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
}
