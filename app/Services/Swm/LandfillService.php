<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillType;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
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
        $landfillTypeMap = LandfillType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $stsLabelMap = Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Sts $sts) {
                $label = trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name);

                return [$sts->id => $label];
            })
            ->all();

        return DataTables::of($query)
            ->filter(function ($q) use ($data) {
                if (! empty($data['landfill_id'] ?? null)) {
                    $q->where('landfill_id', 'ILIKE', '%'.trim((string) $data['landfill_id']).'%');
                }
                if (! empty($data['name'] ?? null)) {
                    $q->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
                }
                if (! empty($data['operator_name'] ?? null)) {
                    $q->where('operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
                }
                if (! empty($data['contact_number'] ?? null)) {
                    $q->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
                }
                if (! empty($data['operational_status'] ?? null)) {
                    $q->where('operational_status', $data['operational_status']);
                }
                if (! empty($data['source_sts_id'] ?? null)) {
                    $q->whereJsonContains('source_sts_ids', (int) $data['source_sts_id']);
                }
                if (! empty($data['waste_type_id'] ?? null)) {
                    $q->whereJsonContains('waste_type_ids', (int) $data['waste_type_id']);
                }
                if (! empty($data['landfill_type_id'] ?? null)) {
                    $q->where('landfill_type_id', (int) $data['landfill_type_id']);
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
                if (array_key_exists('weighbridge_facility_available', $data) && $data['weighbridge_facility_available'] !== '' && $data['weighbridge_facility_available'] !== null) {
                    $q->where('weighbridge_facility_available', filter_var($data['weighbridge_facility_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['weighbridge_facility_available']);
                }
                if (array_key_exists('boundary_wall_available', $data) && $data['boundary_wall_available'] !== '' && $data['boundary_wall_available'] !== null) {
                    $q->where('boundary_wall_available', filter_var($data['boundary_wall_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['boundary_wall_available']);
                }
                if (array_key_exists('lighting_arrangement_available', $data) && $data['lighting_arrangement_available'] !== '' && $data['lighting_arrangement_available'] !== null) {
                    $q->where('lighting_arrangement_available', filter_var($data['lighting_arrangement_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['lighting_arrangement_available']);
                }
                if (array_key_exists('adequate_covering_arrangement_available', $data) && $data['adequate_covering_arrangement_available'] !== '' && $data['adequate_covering_arrangement_available'] !== null) {
                    $q->where('adequate_covering_arrangement_available', filter_var($data['adequate_covering_arrangement_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['adequate_covering_arrangement_available']);
                }
                if (array_key_exists('gas_control_system_available', $data) && $data['gas_control_system_available'] !== '' && $data['gas_control_system_available'] !== null) {
                    $q->where('gas_control_system_available', filter_var($data['gas_control_system_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['gas_control_system_available']);
                }
                if (array_key_exists('leachate_collection_system_available', $data) && $data['leachate_collection_system_available'] !== '' && $data['leachate_collection_system_available'] !== null) {
                    $q->where('leachate_collection_system_available', filter_var($data['leachate_collection_system_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['leachate_collection_system_available']);
                }
            })
            ->editColumn('operational_status', fn ($m) => ucfirst((string) $m->operational_status))
            ->editColumn('segregation_practiced', fn ($m) => is_null($m->segregation_practiced) ? '' : ($m->segregation_practiced ? __('Yes') : __('No')))
            ->editColumn('reuse_practiced', fn ($m) => is_null($m->reuse_practiced) ? '' : ($m->reuse_practiced ? __('Yes') : __('No')))
            ->editColumn('treatment', fn ($m) => is_null($m->treatment) ? '' : ($m->treatment ? __('Yes') : __('No')))
            ->addColumn('landfill_type_name', fn ($m) => $landfillTypeMap[$m->landfill_type_id] ?? '')
            ->addColumn('weighbridge_facility_available_text', fn ($m) => is_null($m->weighbridge_facility_available) ? '' : ($m->weighbridge_facility_available ? __('Yes') : __('No')))
            ->addColumn('boundary_wall_available_text', fn ($m) => is_null($m->boundary_wall_available) ? '' : ($m->boundary_wall_available ? __('Yes') : __('No')))
            ->addColumn('lighting_arrangement_available_text', fn ($m) => is_null($m->lighting_arrangement_available) ? '' : ($m->lighting_arrangement_available ? __('Yes') : __('No')))
            ->addColumn('adequate_covering_arrangement_available_text', fn ($m) => is_null($m->adequate_covering_arrangement_available) ? '' : ($m->adequate_covering_arrangement_available ? __('Yes') : __('No')))
            ->addColumn('gas_control_system_available_text', fn ($m) => is_null($m->gas_control_system_available) ? '' : ($m->gas_control_system_available ? __('Yes') : __('No')))
            ->addColumn('leachate_collection_system_available_text', fn ($m) => is_null($m->leachate_collection_system_available) ? '' : ($m->leachate_collection_system_available ? __('Yes') : __('No')))
            ->addColumn('source_sts_text', function ($m) use ($stsLabelMap) {
                $ids = $m->source_sts_ids ?? [];
                if (empty($ids)) {
                    return '';
                }

                $labels = [];
                foreach ($ids as $id) {
                    if (isset($stsLabelMap[$id])) {
                        $labels[] = $stsLabelMap[$id];
                    }
                }

                return implode(', ', $labels);
            })
            ->addColumn('source_wards_text', function ($m) {
                $wards = $m->source_wards ?? [];

                return empty($wards) ? '' : implode(', ', $wards);
            })
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
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['swm.landfills.destroy', $model->id]]);

                if (Auth::user()->can('Edit SW Landfill')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('Swm\LandfillController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('Swm\LandfillController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }

                if (Auth::user()->can('View SW Landfill History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('Swm\LandfillController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }

                if (Auth::user()->can('Delete SW Landfill')) {
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

        $sourceStsIds = array_values(array_unique(array_map('intval', $data['source_sts_ids'] ?? [])));
        if (empty($sourceStsIds)) {
            $sourceStsIds = null;
        }

        $autoSourceWards = null;
        if (! empty($sourceStsIds)) {
            $autoSourceWards = Sts::query()
                ->whereIn('id', $sourceStsIds)
                ->whereNull('deleted_at')
                ->whereNotNull('ward_no')
                ->pluck('ward_no')
                ->map(fn ($w) => (int) $w)
                ->unique()
                ->sort()
                ->values()
                ->all();
            if (empty($autoSourceWards)) {
                $autoSourceWards = null;
            }
        }

        $sourceWards = null;
        if (array_key_exists('source_wards', $data) && is_array($data['source_wards'])) {
            $sourceWards = array_values(array_unique(array_map('intval', $data['source_wards'])));
            sort($sourceWards);
            if (empty($sourceWards)) {
                $sourceWards = null;
            }
        } else {
            $sourceWards = $autoSourceWards;
        }

        $landfill->name = $data['name'] ?? null;
        $landfill->location = $data['location'] ?? null;
        $landfill->operator_name = $data['operator_name'] ?? null;
        $landfill->contact_number = $data['contact_number'] ?? null;
        $landfill->capacity = $data['capacity'] ?? null;
        $landfill->area = $data['area'] ?? null;
        $landfill->landfill_type_id = $data['landfill_type_id'] ?? null;
        $landfill->source_sts_ids = $sourceStsIds;
        $landfill->source_wards = $sourceWards;
        $landfill->segregation_practiced = ! is_null($data['segregation_practiced'] ?? null) ? (bool) $data['segregation_practiced'] : null;
        $landfill->reuse_practiced = ! is_null($data['reuse_practiced'] ?? null) ? (bool) $data['reuse_practiced'] : null;
        $landfill->waste_type_ids = $data['waste_type_ids'] ?? null;
        $landfill->weighbridge_facility_available = ! is_null($data['weighbridge_facility_available'] ?? null) ? (bool) $data['weighbridge_facility_available'] : null;
        $landfill->boundary_wall_available = ! is_null($data['boundary_wall_available'] ?? null) ? (bool) $data['boundary_wall_available'] : null;
        $landfill->lighting_arrangement_available = ! is_null($data['lighting_arrangement_available'] ?? null) ? (bool) $data['lighting_arrangement_available'] : null;
        $landfill->manpower_deployed = $data['manpower_deployed'] ?? null;
        $landfill->adequate_covering_arrangement_available = ! is_null($data['adequate_covering_arrangement_available'] ?? null) ? (bool) $data['adequate_covering_arrangement_available'] : null;
        $landfill->gas_control_system_available = ! is_null($data['gas_control_system_available'] ?? null) ? (bool) $data['gas_control_system_available'] : null;
        $landfill->leachate_collection_system_available = ! is_null($data['leachate_collection_system_available'] ?? null) ? (bool) $data['leachate_collection_system_available'] : null;
        $landfill->treatment = ! is_null($data['treatment'] ?? null) ? (bool) $data['treatment'] : null;
        $landfill->operational_status = $data['operational_status'] ?? 'active';

        if ($landfill->exists && empty(trim((string) ($landfill->landfill_id ?? '')))) {
            $serial = Landfill::getNextSerial();
            $landfill->landfill_id = Landfill::generateLandfillId($serial);
        }

        $landfill->save();

        return $landfill->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('Landfill ID'),
            __('Landfill Name'),
            __('Location'),
            __('Operator Name'),
            __('Contact Number'),
            __('Capacity'),
            __('Area'),
            __('Landfill Type'),
            __('Source STS'),
            __('Source Wards'),
            __('Segregation Practiced'),
            __('Reuse Practiced'),
            __('Waste Types'),
            __('Weighbridge Facility Available'),
            __('Boundary Wall Around Landfill Area Available'),
            __('Lighting Arrangement at Landfill Site Available'),
            __('Number of Manpower Deployed'),
            __('Adequate Covering Arrangement at Landfill Site Available'),
            __('System for Gas Control from Filled Landfill Available'),
            __('Leachate Collection System Available'),
            __('Treatment'),
            __('Operational Status'),
        ];

        $query = Landfill::query()->whereNull('deleted_at');
        $landfillTypeMap = LandfillType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $wasteTypeMap = WasteType::query()->whereNull('deleted_at')->pluck('name', 'id')->all();
        $stsLabelMap = Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Sts $sts) {
                $label = trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name);

                return [$sts->id => $label];
            })
            ->all();

        if (! empty($data['landfill_id'] ?? null)) {
            $query->where('landfill_id', 'ILIKE', '%'.trim((string) $data['landfill_id']).'%');
        }
        if (! empty($data['name'] ?? null)) {
            $query->where('name', 'ILIKE', '%'.trim((string) $data['name']).'%');
        }
        if (! empty($data['operator_name'] ?? null)) {
            $query->where('operator_name', 'ILIKE', '%'.trim((string) $data['operator_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['operational_status'] ?? null)) {
            $query->where('operational_status', $data['operational_status']);
        }
        if (! empty($data['source_sts_id'] ?? null)) {
            $query->whereJsonContains('source_sts_ids', (int) $data['source_sts_id']);
        }
        if (! empty($data['waste_type_id'] ?? null)) {
            $query->whereJsonContains('waste_type_ids', (int) $data['waste_type_id']);
        }
        if (! empty($data['landfill_type_id'] ?? null)) {
            $query->where('landfill_type_id', (int) $data['landfill_type_id']);
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
        if (array_key_exists('weighbridge_facility_available', $data) && $data['weighbridge_facility_available'] !== '' && $data['weighbridge_facility_available'] !== null) {
            $query->where('weighbridge_facility_available', filter_var($data['weighbridge_facility_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['weighbridge_facility_available']);
        }
        if (array_key_exists('boundary_wall_available', $data) && $data['boundary_wall_available'] !== '' && $data['boundary_wall_available'] !== null) {
            $query->where('boundary_wall_available', filter_var($data['boundary_wall_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['boundary_wall_available']);
        }
        if (array_key_exists('lighting_arrangement_available', $data) && $data['lighting_arrangement_available'] !== '' && $data['lighting_arrangement_available'] !== null) {
            $query->where('lighting_arrangement_available', filter_var($data['lighting_arrangement_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['lighting_arrangement_available']);
        }
        if (array_key_exists('adequate_covering_arrangement_available', $data) && $data['adequate_covering_arrangement_available'] !== '' && $data['adequate_covering_arrangement_available'] !== null) {
            $query->where('adequate_covering_arrangement_available', filter_var($data['adequate_covering_arrangement_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['adequate_covering_arrangement_available']);
        }
        if (array_key_exists('gas_control_system_available', $data) && $data['gas_control_system_available'] !== '' && $data['gas_control_system_available'] !== null) {
            $query->where('gas_control_system_available', filter_var($data['gas_control_system_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['gas_control_system_available']);
        }
        if (array_key_exists('leachate_collection_system_available', $data) && $data['leachate_collection_system_available'] !== '' && $data['leachate_collection_system_available'] !== null) {
            $query->where('leachate_collection_system_available', filter_var($data['leachate_collection_system_available'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['leachate_collection_system_available']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Landfills.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $stsLabelMap, $wasteTypeMap, $landfillTypeMap) {
            foreach ($rows as $row) {
                $sourceSts = [];
                foreach (($row->source_sts_ids ?? []) as $sid) {
                    if (isset($stsLabelMap[$sid])) {
                        $sourceSts[] = $stsLabelMap[$sid];
                    }
                }
                $wasteTypes = [];
                foreach (($row->waste_type_ids ?? []) as $wid) {
                    if (isset($wasteTypeMap[$wid])) {
                        $wasteTypes[] = $wasteTypeMap[$wid];
                    }
                }

                $writer->addRow([
                    $row->landfill_id,
                    $row->name,
                    $row->location,
                    $row->operator_name,
                    $row->contact_number,
                    $row->capacity,
                    $row->area,
                    $landfillTypeMap[$row->landfill_type_id] ?? '',
                    implode(', ', $sourceSts),
                    implode(', ', $row->source_wards ?? []),
                    is_null($row->segregation_practiced) ? '' : ($row->segregation_practiced ? __('Yes') : __('No')),
                    is_null($row->reuse_practiced) ? '' : ($row->reuse_practiced ? __('Yes') : __('No')),
                    implode(', ', $wasteTypes),
                    is_null($row->weighbridge_facility_available) ? '' : ($row->weighbridge_facility_available ? __('Yes') : __('No')),
                    is_null($row->boundary_wall_available) ? '' : ($row->boundary_wall_available ? __('Yes') : __('No')),
                    is_null($row->lighting_arrangement_available) ? '' : ($row->lighting_arrangement_available ? __('Yes') : __('No')),
                    $row->manpower_deployed,
                    is_null($row->adequate_covering_arrangement_available) ? '' : ($row->adequate_covering_arrangement_available ? __('Yes') : __('No')),
                    is_null($row->gas_control_system_available) ? '' : ($row->gas_control_system_available ? __('Yes') : __('No')),
                    is_null($row->leachate_collection_system_available) ? '' : ($row->leachate_collection_system_available ? __('Yes') : __('No')),
                    is_null($row->treatment) ? '' : ($row->treatment ? __('Yes') : __('No')),
                    ucfirst((string) $row->operational_status),
                ]);
            }
        });

        $writer->close();
    }
}
