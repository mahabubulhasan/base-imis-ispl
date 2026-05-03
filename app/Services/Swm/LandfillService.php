<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
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
                if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
                    $q->where('segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
                }
                if (array_key_exists('reuse_practiced', $data) && $data['reuse_practiced'] !== '' && $data['reuse_practiced'] !== null) {
                    $q->where('reuse_practiced', filter_var($data['reuse_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['reuse_practiced']);
                }
                if (array_key_exists('treatment', $data) && $data['treatment'] !== '' && $data['treatment'] !== null) {
                    $q->where('treatment', filter_var($data['treatment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['treatment']);
                }
            })
            ->editColumn('operational_status', fn ($m) => ucfirst((string) $m->operational_status))
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
            ->editColumn('reuse_practiced', fn ($m) => $m->reuse_practiced ? __('Yes') : __('No'))
            ->editColumn('treatment', fn ($m) => $m->treatment ? __('Yes') : __('No'))
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
        $landfill->source_sts_ids = $sourceStsIds;
        $landfill->source_wards = $sourceWards;
        $landfill->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $landfill->reuse_practiced = (bool) ($data['reuse_practiced'] ?? false);
        $landfill->waste_type_ids = $data['waste_type_ids'] ?? null;
        $landfill->monthly_waste_for_composting = $data['monthly_waste_for_composting'] ?? null;
        $landfill->treatment = (bool) ($data['treatment'] ?? false);
        $landfill->operational_status = $data['operational_status'] ?? 'active';
        $landfill->save();

        return $landfill->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('Landfill ID'),
            __('Name'),
            __('Location'),
            __('Operator Name'),
            __('Contact Number'),
            __('Capacity'),
            __('Area'),
            __('Source STS'),
            __('Source Wards'),
            __('Segregation Practiced'),
            __('Reuse Practiced'),
            __('Waste Types'),
            __('Monthly Waste for Composting'),
            __('Treatment'),
            __('Operational Status'),
        ];

        $query = Landfill::query()->whereNull('deleted_at');
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
        if (array_key_exists('segregation_practiced', $data) && $data['segregation_practiced'] !== '' && $data['segregation_practiced'] !== null) {
            $query->where('segregation_practiced', filter_var($data['segregation_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['segregation_practiced']);
        }
        if (array_key_exists('reuse_practiced', $data) && $data['reuse_practiced'] !== '' && $data['reuse_practiced'] !== null) {
            $query->where('reuse_practiced', filter_var($data['reuse_practiced'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['reuse_practiced']);
        }
        if (array_key_exists('treatment', $data) && $data['treatment'] !== '' && $data['treatment'] !== null) {
            $query->where('treatment', filter_var($data['treatment'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['treatment']);
        }

        $style = (new StyleBuilder())
            ->setFontBold()
            ->setFontSize(13)
            ->setBackgroundColor(Color::rgb(228, 228, 228))
            ->build();

        $writer = WriterFactory::create(Type::CSV);
        $writer->openToBrowser('SW Landfills.csv')
            ->addRowWithStyle($columns, $style);

        $query->orderBy('id')->chunk(5000, function ($rows) use ($writer, $stsLabelMap, $wasteTypeMap) {
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
                    implode(', ', $sourceSts),
                    implode(', ', $row->source_wards ?? []),
                    $row->segregation_practiced ? __('Yes') : __('No'),
                    $row->reuse_practiced ? __('Yes') : __('No'),
                    implode(', ', $wasteTypes),
                    $row->monthly_waste_for_composting,
                    $row->treatment ? __('Yes') : __('No'),
                    ucfirst((string) $row->operational_status),
                ]);
            }
        });

        $writer->close();
    }
}
