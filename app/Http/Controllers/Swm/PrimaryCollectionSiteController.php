<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\PrimaryCollectionSiteRequest;
use App\Models\BuildingInfo\Building;
use App\Models\Swm\Lic;
use App\Models\Swm\PrimaryCollectionSite;
use App\Models\Swm\Worker;
use App\Models\UtilityInfo\Roadline;
use App\Services\Swm\PrimaryCollectionSiteService;
use Illuminate\Http\Request;

class PrimaryCollectionSiteController extends Controller
{
    protected PrimaryCollectionSiteService $primaryCollectionSiteService;

    public function __construct(PrimaryCollectionSiteService $primaryCollectionSiteService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Primary Collection Sites', ['only' => ['index', 'getData', 'getBuildingSnapshot']]);
        $this->middleware('permission:View SWM Primary Collection Site', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Primary Collection Site', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Primary Collection Site', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Primary Collection Site', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Primary Collection Sites to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Primary Collection Site History', ['only' => ['history']]);
        $this->primaryCollectionSiteService = $primaryCollectionSiteService;
    }

    protected function bins()
    {
        return Building::query()
            ->whereNull('deleted_at')
            ->orderBy('bin')
            ->pluck('bin', 'bin')
            ->all();
    }

    protected function vanPullers()
    {
        return Worker::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function licOptions()
    {
        return Lic::query()
            ->whereNull('deleted_at')
            ->orderBy('lic_id')
            ->pluck('lic_id', 'lic_id')
            ->all();
    }

    public function index()
    {
        $page_title = __('Primary Collection Sites');

        return view('swm.service-coverage.primary-collection-sites.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->primaryCollectionSiteService->getAllPrimaryCollectionSites($request->all());
    }

    public function getBuildingSnapshot(Request $request)
    {
        $building = Building::query()
            ->with(['functionalUse'])
            ->whereNull('deleted_at')
            ->find($request->input('bin'));

        if (! $building) {
            return response()->json([], 404);
        }

        $roadCode = trim((string) ($building->road_code ?? ''));
        $roadName = null;
        if ($roadCode !== '' && $roadCode !== '0') {
            $roadName = Roadline::query()
                ->where('code', $roadCode)
                ->whereNull('deleted_at')
                ->value('name');
        }

        return response()->json([
            'ward' => $building->ward,
            'road_no_name' => $roadName,
            'holding_number' => $building->house_number,
            'tax_id' => $building->tax_code,
            'bin' => $building->bin,
            'functional_use' => optional($building->functionalUse)->name,
        ]);
    }

    public function create()
    {
        $page_title = __('Add Primary Collection Site');
        $primaryCollectionSite = null;
        $bins = $this->bins();
        $vanPullers = $this->vanPullers();
        $licOptions = $this->licOptions();

        return view('swm.service-coverage.primary-collection-sites.create', compact('page_title', 'primaryCollectionSite', 'bins', 'vanPullers', 'licOptions'));
    }

    public function store(PrimaryCollectionSiteRequest $request)
    {
        $this->primaryCollectionSiteService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.primary-collection-sites.index')->with('success', __('Primary collection site created successfully.'));
    }

    public function show(PrimaryCollectionSite $primaryCollectionSite)
    {
        $page_title = __('Primary Collection Site Details');

        return view('swm.service-coverage.primary-collection-sites.show', compact('page_title', 'primaryCollectionSite'));
    }

    public function edit(PrimaryCollectionSite $primaryCollectionSite)
    {
        $page_title = __('Edit Primary Collection Site');
        $bins = $this->bins();
        $vanPullers = $this->vanPullers();
        $licOptions = $this->licOptions();

        return view('swm.service-coverage.primary-collection-sites.edit', compact('page_title', 'primaryCollectionSite', 'bins', 'vanPullers', 'licOptions'));
    }

    public function update(PrimaryCollectionSiteRequest $request, PrimaryCollectionSite $primaryCollectionSite)
    {
        $this->primaryCollectionSiteService->storeOrUpdate((int) $primaryCollectionSite->id, $request->all());

        return redirect()->route('swm.primary-collection-sites.index')->with('success', __('Primary collection site updated successfully.'));
    }

    public function destroy(PrimaryCollectionSite $primaryCollectionSite)
    {
        $primaryCollectionSite->delete();

        return redirect()->route('swm.primary-collection-sites.index')->with('success', __('Primary collection site deleted successfully.'));
    }

    public function history(PrimaryCollectionSite $primaryCollectionSite)
    {
        $page_title = __('Primary Collection Site History');

        return view('swm.service-coverage.primary-collection-sites.history', compact('page_title', 'primaryCollectionSite'));
    }

    public function export(Request $request)
    {
        return $this->primaryCollectionSiteService->download($request->all());
    }
}
