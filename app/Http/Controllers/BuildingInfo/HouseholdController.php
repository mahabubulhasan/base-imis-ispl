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
        $this->middleware('permission:List Households', ['only' => ['index', 'getData', 'getBuildingSnapshot']]);
        $this->middleware('permission:View Household', ['only' => ['show']]);
        $this->middleware('permission:Add Household', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Household', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Household', ['only' => ['destroy']]);
        $this->middleware('permission:Export Households to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import Households From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View Household History', ['only' => ['history']]);
    }

    protected function bins()
    {
        return Building::query()->whereNull('deleted_at')->orderBy('bin')->pluck('bin', 'bin')->all();
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
            'area_mohalla_name' => $building->house_locality
        ]);
    }

    public function create()
    {
        $page_title = __('Add Household');
        $household = null;
        $bins = $this->bins();
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
        $bins = $this->bins();
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
