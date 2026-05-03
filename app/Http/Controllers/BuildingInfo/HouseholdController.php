<?php

namespace App\Http\Controllers\BuildingInfo;

use App\Http\Controllers\Controller;
use App\Http\Requests\BuildingInfo\HouseholdRequest;
use App\Models\BuildingInfo\Building;
use App\Models\BuildingInfo\FunctionalUse;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Lic;
use App\Models\Swm\WasteBinType;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use App\Services\BuildingInfo\HouseholdService;
use Illuminate\Http\Request;

class HouseholdController extends Controller
{
    public function __construct(protected HouseholdService $householdService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List Households', ['only' => ['index', 'getData', 'getBuildingSnapshot']]);
        $this->middleware('permission:View Household', ['only' => ['show']]);
        $this->middleware('permission:Add Household', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit Household', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete Household', ['only' => ['destroy']]);
        $this->middleware('permission:Export Households to CSV', ['only' => ['export']]);
        $this->middleware('permission:View Household History', ['only' => ['history']]);
    }

    protected function bins()
    {
        return Building::query()->whereNull('deleted_at')->orderBy('bin')->pluck('bin', 'bin')->all();
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

    protected function functionalUseOptions()
    {
        return FunctionalUse::query()->orderBy('name')->pluck('name', 'name')->all();
    }

    protected function wasteBinTypeOptions(): array
    {
        return WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function othersWasteBinTypeId(): ?int
    {
        $id = WasteBinType::query()
            ->where('name', WasteBinType::OTHERS_SPECIFY_NAME)
            ->whereNull('deleted_at')
            ->value('id');

        return $id !== null ? (int) $id : null;
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
        $building = Building::query()->with(['functionalUse'])->whereNull('deleted_at')->find($request->input('bin'));
        if (! $building) {
            return response()->json([], 404);
        }

        $roadCode = trim((string) ($building->road_code ?? ''));
        $roadName = null;
        if ($roadCode !== '' && $roadCode !== '0') {
            $roadName = Roadline::query()->where('code', $roadCode)->whereNull('deleted_at')->value('name');
        }

        return response()->json([
            'ward' => $building->ward,
            'road_no_name' => $roadName,
            'holding_number' => $building->house_number,
            'tax_id' => $building->tax_code,
            'bin' => $building->bin,
            'functional_use' => optional($building->functionalUse)->name,
            'lic_id' => $building->lic_id,
        ]);
    }

    public function create()
    {
        $page_title = __('Add Household');
        $household = null;
        $bins = $this->bins();
        $vanPullers = $this->vanPullers();
        $licOptions = $this->licOptions();
        $functionalUses = $this->functionalUseOptions();
        $wasteBinTypes = $this->wasteBinTypeOptions();
        $othersWasteBinTypeId = $this->othersWasteBinTypeId();

        return view('building-info.households.create', compact(
            'page_title',
            'household',
            'bins',
            'vanPullers',
            'licOptions',
            'functionalUses',
            'wasteBinTypes',
            'othersWasteBinTypeId'
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
        $functionalUses = $this->functionalUseOptions();
        $wasteBinTypes = $this->wasteBinTypeOptions();
        $othersWasteBinTypeId = $this->othersWasteBinTypeId();

        return view('building-info.households.edit', compact(
            'page_title',
            'household',
            'bins',
            'vanPullers',
            'licOptions',
            'functionalUses',
            'wasteBinTypes',
            'othersWasteBinTypeId'
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
}
