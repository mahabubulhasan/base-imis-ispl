<?php

namespace App\Services\Swm;

use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillType;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use App\Support\Swm\SwmImportTemplateOptions;
use Auth;
use Yajra\DataTables\DataTables;

class LandfillService
{
    use HasExcelColumnValidationLabels;

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
        $landfill->waste_type_ids = $data['waste_type_ids'] ?? null;
        $landfill->weighbridge_facility_available = ! is_null($data['weighbridge_facility_available'] ?? null) ? (bool) $data['weighbridge_facility_available'] : null;
        $landfill->boundary_wall_available = ! is_null($data['boundary_wall_available'] ?? null) ? (bool) $data['boundary_wall_available'] : null;
        $landfill->lighting_arrangement_available = ! is_null($data['lighting_arrangement_available'] ?? null) ? (bool) $data['lighting_arrangement_available'] : null;
        $landfill->manpower_deployed = $data['manpower_deployed'] ?? null;
        $landfill->adequate_covering_arrangement_available = ! is_null($data['adequate_covering_arrangement_available'] ?? null) ? (bool) $data['adequate_covering_arrangement_available'] : null;
        $landfill->gas_control_system_available = ! is_null($data['gas_control_system_available'] ?? null) ? (bool) $data['gas_control_system_available'] : null;
        $landfill->leachate_collection_system_available = ! is_null($data['leachate_collection_system_available'] ?? null) ? (bool) $data['leachate_collection_system_available'] : null;
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
        $columnDefs = $this->excelColumnDefinitions();
        $headers = SwmExcelColumns::exportHeaders($columnDefs);

        $query = Landfill::query()->whereNull('deleted_at');
        $this->applyExportFilters($query, $data);

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

        $rows = [];
        $query->orderBy('id')->chunk(5000, function ($chunk) use (&$rows, $columnDefs, $stsLabelMap, $wasteTypeMap, $landfillTypeMap) {
            foreach ($chunk as $row) {
                $rows[] = SwmExcelColumns::buildExportRow(
                    $columnDefs,
                    $row,
                    fn (string $key, Landfill $model) => $this->formatLandfillExportValue($key, $model, $stsLabelMap, $wasteTypeMap, $landfillTypeMap)
                );
            }
        });

        (new SwmExcelTemplateWriter())->downloadData(SwmExcelFilename::export('landfills'), $headers, $rows);
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            SwmExcelFilename::importTemplate('landfills'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, array{key: string, label: string, export?: bool, import?: bool, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    protected function excelColumnDefinitions(): array
    {
        $landfillTypes = LandfillType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name')->all();
        $yesNo = SwmImportTemplateOptions::yesNo();

        return [
            ['key' => 'landfill_id', 'label' => __('Landfill ID'), 'import' => false, 'template' => false, 'derived' => true],
            ['key' => 'name', 'label' => __('Landfill Name'), 'required' => true],
            ['key' => 'location', 'label' => __('Location')],
            ['key' => 'operator_name', 'label' => __('Operator Name'), 'required' => true],
            ['key' => 'contact_number', 'label' => __("Operator's Contact Number"), 'required' => true],
            ['key' => 'capacity', 'label' => __('Capacity').' ('.__('Ton').')'],
            ['key' => 'area', 'label' => __('Area').' ('.__('Acre').')'],
            ['key' => 'landfill_type', 'label' => __('Landfill Type'), 'dropdown' => $landfillTypes],
            [
                'key' => 'source_sts',
                'label' => __('Source STSs'),
                'multiselect' => true,
                'dropdown' => SwmImportTemplateOptions::stsLabels(),
                'reference_key' => 'source_sts',
            ],
            ['key' => 'sts_source_wards', 'label' => __('STS Source Wards'), 'import' => false, 'template' => true, 'derived' => true],
            [
                'key' => 'source_wards',
                'label' => __('Other Source Wards'),
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
            ['key' => 'weighbridge_facility_available', 'label' => __('Weighbridge Facility Available?'), 'dropdown' => $yesNo],
            ['key' => 'boundary_wall_available', 'label' => __('Boundary Wall Around the Landfill Area Available?'), 'dropdown' => $yesNo],
            ['key' => 'lighting_arrangement_available', 'label' => __('Lighting Arrangement at the Landfill Site Available?'), 'dropdown' => $yesNo],
            ['key' => 'manpower_deployed', 'label' => __('Number of Manpower Deployed at the Landfill Site')],
            ['key' => 'adequate_covering_arrangement_available', 'label' => __('Adequate Covering Arrangement at the Landfill Site Available?'), 'dropdown' => $yesNo],
            ['key' => 'gas_control_system_available', 'label' => __('System for Gas Control from the Filled Landfill Available?'), 'dropdown' => $yesNo],
            ['key' => 'leachate_collection_system_available', 'label' => __('Leachate Collection System Available?'), 'dropdown' => $yesNo],
            ['key' => 'operational_status', 'label' => __('Operational Status'), 'required' => true, 'dropdown' => ['active', 'inactive']],
        ];
    }

    protected function applyExportFilters($query, array $data): void
    {
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
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>, multiselect?: bool, reference_key?: string}> */
    public function importColumnDefinitions(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /**
     * @param  array<int|string, string>  $stsLabelMap
     * @param  array<int|string, string>  $wasteTypeMap
     * @param  array<int|string, string>  $landfillTypeMap
     */
    protected function formatLandfillExportValue(string $key, Landfill $row, array $stsLabelMap, array $wasteTypeMap, array $landfillTypeMap): mixed
    {
        $yesNo = static fn (?bool $value) => is_null($value) ? '' : ($value ? __('Yes') : __('No'));

        return match ($key) {
            'landfill_id' => $row->landfill_id,
            'name' => $row->name,
            'location' => $row->location,
            'operator_name' => $row->operator_name,
            'contact_number' => $row->contact_number,
            'capacity' => $row->capacity,
            'area' => $row->area,
            'landfill_type' => $landfillTypeMap[$row->landfill_type_id] ?? '',
            'source_sts' => implode(', ', array_values(array_filter(array_map(
                fn ($sid) => $stsLabelMap[$sid] ?? null,
                $row->source_sts_ids ?? []
            )))),
            'sts_source_wards' => implode(', ', $this->unionWardsForSourceStsIds($row->source_sts_ids ?? [])),
            'source_wards' => implode(', ', $row->source_wards ?? []),
            'waste_types' => implode(', ', array_values(array_filter(array_map(
                fn ($wid) => $wasteTypeMap[$wid] ?? null,
                $row->waste_type_ids ?? []
            )))),
            'segregation_practiced' => $yesNo($row->segregation_practiced),
            'weighbridge_facility_available' => $yesNo($row->weighbridge_facility_available),
            'boundary_wall_available' => $yesNo($row->boundary_wall_available),
            'lighting_arrangement_available' => $yesNo($row->lighting_arrangement_available),
            'manpower_deployed' => $row->manpower_deployed,
            'adequate_covering_arrangement_available' => $yesNo($row->adequate_covering_arrangement_available),
            'gas_control_system_available' => $yesNo($row->gas_control_system_available),
            'leachate_collection_system_available' => $yesNo($row->leachate_collection_system_available),
            'operational_status' => $row->operational_status,
            default => '',
        };
    }

    /**
     * @param  array<int, int>|null  $stsIds
     * @return list<string>
     */
    protected function unionWardsForSourceStsIds(?array $stsIds): array
    {
        if (empty($stsIds)) {
            return [];
        }

        $wardSet = [];
        Sts::query()
            ->whereIn('id', $stsIds)
            ->whereNull('deleted_at')
            ->get(['source_wards', 'ward_no'])
            ->each(function (Sts $sts) use (&$wardSet) {
                foreach ($sts->source_wards ?? [] as $ward) {
                    $wardSet[(string) $ward] = true;
                }
                if ($sts->ward_no) {
                    $wardSet[(string) $sts->ward_no] = true;
                }
            });

        $wards = array_keys($wardSet);
        sort($wards, SORT_NATURAL);

        return array_values($wards);
    }

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'landfill_type_id' => __('Landfill Type'),
            'source_sts_ids' => __('Source STSs'),
            'waste_type_ids' => __('Waste Type'),
        ];
    }
}
