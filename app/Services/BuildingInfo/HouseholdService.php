<?php

namespace App\Services\BuildingInfo;

use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\LayerInfo\Ward;
use App\Services\Formatting\Currency;
use App\Services\Formatting\CurrencyFormatter;
use App\Services\Swm\Concerns\HasExcelColumnValidationLabels;
use App\Models\Swm\WasteBin;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use App\Support\Swm\SwmExcelColumns;
use App\Support\Swm\SwmExcelExportWriter;
use App\Support\Swm\SwmImportRowHelper;
use App\Support\Swm\SwmExcelFilename;
use App\Support\Swm\SwmExcelTemplateWriter;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;

class HouseholdService
{
    use HasExcelColumnValidationLabels;

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
            ->editColumn('waste_charge', fn ($m) => $m->waste_charge !== null
                ? $this->currencyFormatter->format(Currency::TK, $m->waste_charge)
                : '')
            ->editColumn('segregation_practiced', fn ($m) => $m->segregation_practiced ? __('Yes') : __('No'))
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
        $household->ward = $data['ward'] ?? ($building?->ward ?? null);
        $household->road_no = $roadNo !== '' ? $roadNo : null;
        $household->road_name = $roadName !== '' ? $roadName : null;
        $household->holding_number = $data['holding_number'] ?? ($building?->house_number ?? null);
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
        $columns = $this->excelColumnDefinitions();
        $headers = SwmExcelColumns::exportHeaders($columns);

        $query = Household::query()
            ->with(['lic:id,community_name', 'vanPuller:id,name'])
            ->whereNull('deleted_at')
            ->orderBy('id');
        $this->applyHouseholdFilters($query, $data);

        (new SwmExcelExportWriter())->download(SwmExcelFilename::export('households'), $headers, function ($sheet, $colLetter) use ($query, $columns) {
            $rowNum = 2;
            $query->chunk(5000, function ($rows) use ($sheet, $colLetter, $columns, &$rowNum) {
                foreach ($rows as $row) {
                    $values = SwmExcelColumns::buildExportRow($columns, $row, fn (string $key, $model) => $this->formatHouseholdExportValue($key, $model));
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
            SwmExcelFilename::importTemplate('households'),
            SwmExcelColumns::templateColumns($this->excelColumnDefinitions())
        );
    }

    /** @return array<int, string> */
    public function requiredImportLabels(): array
    {
        return SwmExcelColumns::requiredImportLabels($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label?: string, required?: bool, dropdown?: array<int, string>}> */
    public function importColumnDefinitions(): array
    {
        return $this->importTemplateColumns();
    }

    public function importTemplateColumns(): array
    {
        return SwmExcelColumns::importTemplateColumns($this->excelColumnDefinitions());
    }

    /** @return array<int, array{key: string, label: string, required?: bool, dropdown?: array<int, string>}> */
    public function excelColumnDefinitions(): array
    {
        $yesNo = [__('Yes'), __('No')];
        $statusLabels = array_values(Household::statusOptions());
        $wards = array_map('strval', array_keys(Ward::getInAscOrder()));

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
            ['key' => 'household_id', 'label' => __('Household ID'), 'required' => true],
            ['key' => 'household_owner_name', 'label' => __('Household Owner Name'), 'required' => true],
            ['key' => 'father_or_husband_name', 'label' => __("Father's/Husband's Name")],
            ['key' => 'contact_number', 'label' => __('Contact Number'), 'required' => true],
            ['key' => 'bin', 'label' => __('BIN')],
            ['key' => 'area_mohalla_name', 'label' => __('Sub Location')],
            ['key' => 'ward', 'label' => __('Ward No.'), 'required' => true, 'dropdown' => $wards],
            ['key' => 'road_no', 'label' => __('Road No.')],
            ['key' => 'road_name', 'label' => __('Road Name'), 'required' => true],
            ['key' => 'holding_number', 'label' => __('Holding Number'), 'required' => true],
            ['key' => 'tax_id', 'label' => __('Tax ID')],
            ['key' => 'waste_charge', 'label' => __('Waste Collection Fee').' ('.__('Taka').'/'.__('Month').')'],
            ['key' => 'number_of_family_members', 'label' => __('Number of Family Members')],
            ['key' => 'using_this_service_since', 'label' => __('Using This Service Since'), 'date_hint' => '02 Jun 2026'],
            ['key' => 'daily_waste_volume', 'label' => __('Average Waste Collected').' ('.__('Kg').'/'.__('Day').')'],
            ['key' => 'van_puller', 'label' => __('Van Puller'), 'dropdown' => $vanPullers],
            ['key' => 'is_owner', 'label' => __('Building Owner?'), 'dropdown' => $yesNo],
            // Parent-level flag only; nested waste_bins[*] rows are managed in the form, not via Excel.
            ['key' => 'waste_bin_provided', 'label' => __('Waste Bin Provided?'), 'dropdown' => $yesNo],
            ['key' => 'is_lic', 'label' => __('LIC?'), 'dropdown' => $yesNo],
            ['key' => 'lic_id', 'label' => __('LIC ID'), 'dropdown' => $licOptions],
            ['key' => 'segregation_practiced', 'label' => __('Segregation Practiced?'), 'dropdown' => $yesNo],
            ['key' => 'status', 'label' => __('Household Status'), 'required' => true, 'dropdown' => $statusLabels],
            ['key' => 'remarks', 'label' => __('Remarks')],
            ['key' => 'survey_date', 'label' => __('Survey Date'), 'date_hint' => '02 Jun 2026'],
        ];
    }

    protected function formatHouseholdExportValue(string $key, Household $row): mixed
    {
        return match ($key) {
            'household_id' => $row->household_id,
            'household_owner_name' => $row->household_owner_name,
            'father_or_husband_name' => $row->father_or_husband_name,
            'contact_number' => $row->contact_number,
            'bin' => $row->bin,
            'area_mohalla_name' => $row->area_mohalla_name,
            'ward' => $row->ward,
            'road_no' => $row->road_no,
            'road_name' => $row->road_name,
            'holding_number' => $row->holding_number,
            'tax_id' => $row->tax_id,
            'waste_charge' => $row->waste_charge !== null
                ? $this->currencyFormatter->format(Currency::TK, $row->waste_charge)
                : '',
            'number_of_family_members' => $row->number_of_family_members,
            'using_this_service_since' => SwmImportRowHelper::exportDate($row->using_this_service_since),
            'daily_waste_volume' => $row->daily_waste_volume,
            'van_puller' => $row->vanPuller
                ? "{$row->vanPuller->name} - {$row->vanPuller->id}"
                : '',
            'is_owner' => $row->is_owner ? __('Yes') : __('No'),
            'waste_bin_provided' => $row->waste_bin_provided ? __('Yes') : __('No'),
            'is_lic' => $row->is_lic ? __('Yes') : __('No'),
            'lic_id' => $row->lic
                ? "{$row->lic->community_name} - {$row->lic->id}"
                : '',
            'segregation_practiced' => $row->segregation_practiced ? __('Yes') : __('No'),
            'status' => Household::statusOptions()[$row->status] ?? (string) $row->status,
            'remarks' => $row->remarks,
            'survey_date' => SwmImportRowHelper::exportDate($row->survey_date),
            default => '',
        };
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

    /** @return array<string, string> */
    protected function formOnlyValidationLabels(): array
    {
        return [
            'van_puller_id' => __('Van Puller'),
            'waste_bins' => __('Waste Bins'),
            'waste_bins.*.waste_bin_type_id' => __('Waste Bin Type'),
            'waste_bins.*.total_capacity_kg' => __('Capacity (kg)'),
        ];
    }
}
