<?php

namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\BuildingInfo\HouseholdRequest;
use App\Imports\BuildingInfo\HouseholdImport;
use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\WasteBinType;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use App\Services\BuildingInfo\HouseholdService;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    use HandlesSwmExcelImport;

    public function __construct(protected HouseholdService $householdService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Households', ['only' => ['index', 'getData', 'getBuildingSnapshot', 'binOptions']]);
        $this->middleware('permission:View Household', ['only' => ['show']]);
        $this->middleware('permission:Add Household', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Household', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Household', ['only' => ['destroy']]);
        $this->middleware('permission:Export Households to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import Households From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View Household History', ['only' => ['history']]);
    }

    /**
     * Preselected BIN option(s) for the household form's select2. The full list
     * loads on demand via AJAX (binOptions), so only the household's current bin
     * plus any old('bin') from a failed submit need a server-rendered option.
     *
     * @return array<string, string>  bin => label
     */
    protected function bins(?Household $household = null): array
    {
        $bins = collect([$household?->bin, old('bin')])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->values();

        if ($bins->isEmpty()) {
            return [];
        }

        return Building::query()
            ->whereNull('deleted_at')
            ->whereIn('bin', $bins->all())
            ->orderBy('bin')
            ->get(['bin', 'house_number'])
            ->mapWithKeys(fn ($b) => [
                $b->bin => $b->house_number ? $b->bin.' - '.$b->house_number : (string) $b->bin,
            ])
            ->all();
    }

    /**
     * Searchable, paginated building-BIN options for the household form's
     * select2 (server-side source). Returns { results: [{id, text}], pagination: { more } }.
     */
    public function binOptions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $page = max(1, (int) $request->query('page', 1));
        $limit = 15;

        $query = Building::query()->whereNull('deleted_at');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('bin', 'ilike', '%'.$search.'%')
                    ->orWhere('house_number', 'ilike', '%'.$search.'%');
            });
        }

        $total = $query->count();
        $buildings = $query
            ->orderBy('bin')
            ->offset(($page - 1) * $limit)
            ->limit($limit)
            ->get(['bin', 'house_number']);

        $results = $buildings->map(fn ($b) => [
            'id' => $b->bin,
            'text' => $b->house_number
                ? $b->bin.' - '.$b->house_number
                : (string) $b->bin,
        ])->all();

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $page * $limit < $total],
        ]);
    }

    protected function wards(): array
    {
        return Ward::getInAscOrder();
    }

    protected function vanPullers()
    {
        $rows = Worker::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']);
        $out = [];
        foreach ($rows as $row) {
            $out[$row->id] = "{$row->name} - {$row->id}";
        }
        return $out;
    }

    protected function licOptions()
    {
        $rows = Lic::query()->whereNull('deleted_at')->orderBy('community_name')->get(['id', 'community_name']);
        $out = [];
        foreach ($rows as $row) {
            $out[$row->id] = "{$row->community_name} - {$row->id}";
        }
        return $out;
    }

    protected function wasteBinTypeOptions(): array
    {
        return WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    public function index()
    {
        $page_title = __('Households');
        return view('building-info.households.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->householdService->getAllHouseholds($request->all());
    }

    public function getBuildingSnapshot(Request $request)
    {
        $building = Building::query()->whereNull('deleted_at')->find($request->input('bin'));
        if (! $building) {
            return response()->json([], 404);
        }

        $roadCode = trim((string) ($building->road_code ?? ''));
        $roadName = null;
        if ($roadCode !== '' && $roadCode !== '0') {
            $roadName = Roadline::query()->where('code', $roadCode)->whereNull('deleted_at')->value('name');
        }

        $roadCodeOut = ($roadCode !== '' && $roadCode !== '0') ? $roadCode : null;

        return response()->json([
            'ward' => $building->ward,
            'road_no' => $roadCodeOut,
            'road_name' => $roadName,
            'holding_number' => $building->house_number,
            'tax_id' => $building->tax_code,
            'bin' => $building->bin,
            'lic_id' => $building->lic_id,
            'area_mohalla_name' => $building->house_locality,
            'low_income_hh' => in_array($building->low_income_hh, [true, 1, '1', 't', 'true'], true),
        ]);
    }

    public function create()
    {
        $page_title = __('Add Household');
        $household = null;
        $bins = $this->bins();
        // create: preselected bin only from old('bin') on a failed submit
        $vanPullers = $this->vanPullers();
        $licOptions = $this->licOptions();
        $wasteBinTypes = $this->wasteBinTypeOptions();
        $wards = $this->wards();
        return view('building-info.households.create', compact(
            'page_title',
            'household',
            'bins',
            'vanPullers',
            'licOptions',
            'wasteBinTypes',
            'wards'
        ));
    }

    public function store(HouseholdRequest $request)
    {
        $this->householdService->storeOrUpdate(null, $request->validated());
        return redirect()->route('building-info.households.index')->with('success', __('Household created successfully.'));
    }

    public function show(Household $household)
    {
        $household->load(['wasteBins.wasteBinType']);
        $page_title = __('Household Details');

        return view('building-info.households.show', compact('page_title', 'household'));
    }

    public function edit(Household $household)
    {
        $household->load(['wasteBins.wasteBinType']);
        $page_title = __('Edit Household');
        $bins = $this->bins($household);
        $vanPullers = $this->vanPullers();
        $licOptions = $this->licOptions();
        $wasteBinTypes = $this->wasteBinTypeOptions();
        $wards = $this->wards();
        return view('building-info.households.edit', compact(
            'page_title',
            'household',
            'bins',
            'vanPullers',
            'licOptions',
            'wasteBinTypes',
            'wards'
        ));
    }

    public function update(HouseholdRequest $request, Household $household)
    {
        $this->householdService->storeOrUpdate((int) $household->id, $request->validated());
        return redirect()->route('building-info.households.index')->with('success', __('Household updated successfully.'));
    }

    public function destroy(Household $household)
    {
        $household->delete();
        return redirect()->route('building-info.households.index')->with('success', __('Household deleted successfully.'));
    }

    public function history(Household $household)
    {
        $page_title = __('Household History');
        return view('building-info.households.history', compact('page_title', 'household'));
    }

    public function export(Request $request)
    {
        return $this->householdService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->householdService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Households'),
            route('building-info.households.index'),
            'building-info.households.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            request: $request,
            importClass: HouseholdImport::class,
            requiredHeaders: $this->householdService->requiredImportLabels(),
            indexRoute: 'building-info.households.index',
            disk: 'importswm',
            filenamePrefix: 'households',
            entityName: __('Households'),
        );
    }
}
