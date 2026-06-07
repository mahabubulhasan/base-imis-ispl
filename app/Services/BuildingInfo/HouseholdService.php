<?php

namespace App\Services\BuildingInfo;

use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\LayerInfo\Ward;
use App\Services\Formatting\Currency;
use App\Services\Formatting\CurrencyFormatter;
use App\Models\Swm\WasteBin;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use App\Support\Swm\SwmExcelExportWriter;
use App\Support\Swm\SwmExcelTemplateWriter;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class HouseholdService
{
    public function __construct(
        protected CurrencyFormatter $currencyFormatter,
    ) {
    }

    public function getAllHouseholds(array $data)
    {
        $query = Household::query()->whereNull('deleted_at');

        return DataTables::of($query)
            ->filter(fn ($q) => $this->applyHouseholdFilters($q, $data))
            ->editColumn('is_owner', fn ($m) => $m->is_owner ? __('Yes') : __('No'))
            ->editColumn('is_lic', fn ($m) => $m->is_lic ? __('Yes') : __('No'))
            ->editColumn('survey_date', fn ($m) => $m->survey_date?->format('Y-m-d') ?? '')
            ->editColumn('status', fn ($m) => Household::statusOptions()[$m->status] ?? (string) $m->status)
            ->addColumn('action', function ($model) {
                $content = \Form::open(['method' => 'DELETE', 'route' => ['building-info.households.destroy', $model->id]]);
                if (Auth::user()->can('Edit Household')) {
                    $content .= '<a title="'.__('Edit').'" href="'.action('BuildingInfo\HouseholdController@edit', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-edit"></i></a> ';
                }
                if (Auth::user()->can('View Household')) {
                    $content .= '<a title="'.__('Detail').'" href="'.action('BuildingInfo\HouseholdController@show', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-list"></i></a> ';
                }
                if (Auth::user()->can('View Household History')) {
                    $content .= '<a title="'.__('History').'" href="'.action('BuildingInfo\HouseholdController@history', [$model->id]).'" class="btn btn-info btn-sm mb-1"><i class="fa fa-history"></i></a> ';
                }
                if (Auth::user()->can('Delete Household')) {
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
        $building = null;
        if (! empty($data['bin'] ?? null)) {
            $building = Building::query()->find($data['bin']);
        }

        $roadNo = isset($data['road_no']) ? trim((string) $data['road_no']) : '';
        $roadName = isset($data['road_name']) ? trim((string) $data['road_name']) : '';
        if ($building) {
            $roadCode = trim((string) ($building->road_code ?? ''));
            if ($roadCode !== '' && $roadCode !== '0') {
                if ($roadNo === '') {
                    $roadNo = $roadCode;
                }
                if ($roadName === '') {
                    $resolved = Roadline::query()->where('code', $roadCode)->whereNull('deleted_at')->value('name');
                    $roadName = trim((string) ($resolved ?? ''));
                }
            }
        }

        $household = $id ? Household::find($id) : new Household();
        if (! $household) {
            return null;
        }
        $oldBin = $id ? $household->bin : null;

        $vanPuller = null;
        if (! empty($data['van_puller_id'] ?? null)) {
            $vanPuller = Worker::query()->whereNull('deleted_at')->find($data['van_puller_id']);
        }

        $household->household_id = $data['household_id'] ?? null;
        $household->household_owner_name = $data['household_owner_name'] ?? null;
        $household->father_or_husband_name = $data['father_or_husband_name'] ?? null;
        $household->status = $data['status'] ?? Household::STATUS_ACTIVE;
        $household->contact_number = $data['contact_number'] ?? null;
        $household->area_mohalla_name = $data['area_mohalla_name'] ?? null;
        $household->sub_location = $data['sub_location'] ?? null;
        $household->bin = $data['bin'] ?? null;
        $household->ward = $building?->ward ?? ($data['ward'] ?? null);
        $household->road_no = $roadNo !== '' ? $roadNo : null;
        $household->road_name = $roadName !== '' ? $roadName : null;
        $household->holding_number = $building?->house_number ?? ($data['holding_number'] ?? null);
        $household->tax_id = $data['tax_id'] ?? ($building?->tax_code);
        $household->waste_charge = $data['waste_charge'] ?? null;
        $household->is_owner = (bool) ($data['is_owner'] ?? false);
        $household->is_lic = (bool) ($data['is_lic'] ?? false);
        $household->lic_id = $data['lic_id'] ?? ($building?->lic_id);
        $household->number_of_family_members = $data['number_of_family_members'] ?? null;
        $household->using_this_service_since = $data['using_this_service_since'] ?? null;
        $household->segregation_practiced = (bool) ($data['segregation_practiced'] ?? false);
        $household->waste_bin_provided = (bool) ($data['waste_bin_provided'] ?? false);
        $household->daily_waste_volume = $data['daily_waste_volume'] ?? null;
        $household->survey_date = $data['survey_date'] ?? null;
        $household->remarks = $data['remarks'] ?? null;
        $household->van_puller_id = $data['van_puller_id'] ?? null;
        $household->van_puller_name = $vanPuller ? $vanPuller->name : null;
        $household->save();
        app(BuildingHouseholdLinkService::class)->syncHouseholdBinChange(
            (string) $household->household_id,
            $oldBin,
            $household->bin
        );

        if (! $household->is_owner || ! $household->waste_bin_provided) {
            WasteBin::where('household_id', $household->id)->delete();
        } elseif (! empty($data['waste_bins']) && is_array($data['waste_bins'])) {
            $household->loadMissing('building');
            $fallbackRoadNo = trim((string) ($household->building?->road_code ?? ''));
            $submittedIds = [];
            foreach ($data['waste_bins'] as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $payload = [
                    'household_id' => $household->id,
                    'waste_bin_type_id' => (int) ($row['waste_bin_type_id'] ?? 0),
                    'type_other_detail' => null,
                    'total_capacity_kg' => $row['total_capacity_kg'],
                    'placed_at_buildings' => true,
                    'bin' => $household->bin,
                    'sub_location' => $household->sub_location,
                    'ward_no' => $household->ward,
                    'road_name' => $household->road_name,
                    'road_no' => $household->road_no ?? ($fallbackRoadNo !== '' && $fallbackRoadNo !== '0' ? $fallbackRoadNo : null),
                ];
                $rowId = isset($row['id']) ? (int) $row['id'] : null;
                if ($rowId) {
                    $wasteBin = WasteBin::query()
                        ->where('household_id', $household->id)
                        ->whereNull('deleted_at')
                        ->find($rowId);
                    if ($wasteBin) {
                        $wasteBin->fill($payload);
                        $wasteBin->save();
                        $submittedIds[] = $wasteBin->id;
                    }
                } else {
                    $wasteBin = new WasteBin;
                    $wasteBin->fill($payload);
                    $wasteBin->save();
                    $submittedIds[] = $wasteBin->id;
                }
            }
            WasteBin::query()
                ->where('household_id', $household->id)
                ->whereNull('deleted_at')
                ->whereNotIn('id', $submittedIds)
                ->delete();
        }

        return $household->id;
    }

    public function download(array $data): void
    {
        $columns = [
            __('Household ID'), __('Household Owner Name'), __("Father's/Husband's Name"), __('Status'), __('Contact Number'), __('Ward'),
            __('Area / Mohalla Name'), __('Sub Location'), __('Road No.'), __('Road Name'), __('Holding Number'),
            __('Tax ID'), __('BIN'), __('Waste collection fee (BDT/Month)'),
            __('Building owner (Yes/No)'), __('LIC'),
            __('LIC ID'), __('Survey Date'),
        ];

        $query = Household::query()->whereNull('deleted_at')->orderBy('id');
        $this->applyHouseholdFilters($query, $data);

        (new SwmExcelExportWriter())->download('Households.xlsx', $columns, function ($sheet, $colLetter) use ($query) {
            $rowNum = 2;
            $query->chunk(5000, function ($rows) use ($sheet, $colLetter, &$rowNum) {
                foreach ($rows as $row) {
                    $values = [
                        $row->household_id,
                        $row->household_owner_name,
                        $row->father_or_husband_name,
                        Household::statusOptions()[$row->status] ?? (string) $row->status,
                        $row->contact_number,
                        $row->ward,
                        $row->area_mohalla_name,
                        $row->sub_location,
                        $row->road_no,
                        $row->road_name,
                        $row->holding_number,
                        $row->tax_id,
                        $row->bin,
                        $this->currencyFormatter->format(Currency::TK, $row->waste_charge),
                        $row->is_owner ? __('Yes') : __('No'),
                        $row->is_lic ? __('Yes') : __('No'),
                        $row->lic_id,
                        $row->survey_date?->format('Y-m-d'),
                    ];
                    foreach ($values as $index => $value) {
                        $sheet->setCellValue($colLetter($index + 1).$rowNum, $value);
                    }
                    $rowNum++;
                }
            });
        });
    }

    public function downloadTemplate(): void
    {
        (new SwmExcelTemplateWriter())->download(
            'households-import-template.xlsx',
            $this->importTemplateColumns()
        );
    }

    /** @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>}> */
    public function importTemplateColumns(): array
    {
        $yesNo = [__('Yes'), __('No')];
        $statusLabels = array_values(Household::statusOptions());
        $wards = array_map('strval', array_keys(Ward::getInAscOrder()));

        $bins = Building::query()
            ->whereNull('deleted_at')
            ->orderBy('bin')
            ->pluck('bin')
            ->all();

        $licOptions = Lic::query()
            ->whereNull('deleted_at')
            ->orderBy('community_name')
            ->get(['id', 'community_name'])
            ->map(fn ($row) => "{$row->community_name} - {$row->id}")
            ->all();

        $vanPullers = Worker::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($row) => "{$row->name} - {$row->id}")
            ->all();

        return [
            ['key' => 'household_id', 'required' => true],
            ['key' => 'household_owner_name', 'required' => true],
            ['key' => 'father_or_husband_name'],
            ['key' => 'status', 'required' => true, 'dropdown' => $statusLabels],
            ['key' => 'contact_number', 'required' => true],
            ['key' => 'ward', 'required' => true, 'dropdown' => $wards],
            ['key' => 'area_mohalla_name'],
            ['key' => 'sub_location'],
            ['key' => 'road_no'],
            ['key' => 'road_name', 'required' => true],
            ['key' => 'holding_number', 'required' => true],
            ['key' => 'tax_id'],
            ['key' => 'waste_charge'],
            ['key' => 'bin', 'dropdown' => $bins],
            ['key' => 'is_owner', 'dropdown' => $yesNo],
            ['key' => 'is_lic', 'dropdown' => $yesNo],
            ['key' => 'lic_id', 'dropdown' => $licOptions],
            ['key' => 'number_of_family_members'],
            ['key' => 'daily_waste_volume'],
            ['key' => 'segregation_practiced', 'dropdown' => $yesNo],
            ['key' => 'waste_bin_provided', 'dropdown' => $yesNo],
            ['key' => 'using_this_service_since'],
            ['key' => 'survey_date'],
            ['key' => 'van_puller', 'dropdown' => $vanPullers],
            ['key' => 'remarks'],
        ];
    }

    private function applyHouseholdFilters(Builder $query, array $data): void
    {
        if (! empty($data['household_id'] ?? null)) {
            $query->where('household_id', 'ILIKE', '%'.trim((string) $data['household_id']).'%');
        }
        if (! empty($data['household_owner_name'] ?? null)) {
            $query->where('household_owner_name', 'ILIKE', '%'.trim((string) $data['household_owner_name']).'%');
        }
        if (! empty($data['father_or_husband_name'] ?? null)) {
            $query->where('father_or_husband_name', 'ILIKE', '%'.trim((string) $data['father_or_husband_name']).'%');
        }
        if (! empty($data['contact_number'] ?? null)) {
            $query->where('contact_number', 'ILIKE', '%'.trim((string) $data['contact_number']).'%');
        }
        if (! empty($data['bin'] ?? null)) {
            $query->where('bin', 'ILIKE', '%'.trim((string) $data['bin']).'%');
        }
        if (array_key_exists('is_lic', $data) && $data['is_lic'] !== '' && $data['is_lic'] !== null) {
            $query->where('is_lic', filter_var($data['is_lic'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $data['is_lic']);
        }
        if (! empty($data['lic_id'] ?? null)) {
            $query->where('lic_id', $data['lic_id']);
        }
        if (! empty($data['survey_date'] ?? null)) {
            $query->whereDate('survey_date', $data['survey_date']);
        }
        if (! empty($data['status'] ?? null)) {
            $query->where('status', $data['status']);
        }
    }
}
