<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\WasteBinRequest;
use App\Imports\WasteBinImport;
use App\Models\BuildingInfo\Building;
use App\Models\LayerInfo\Ward;
use App\Models\UtilityInfo\Roadline;
use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Services\Swm\WasteBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteBinController extends Controller
{
    use HandlesSwmExcelImport;

    public function __construct(protected WasteBinService $wasteBinService)
    {
        $this->middleware('auth');
        $this->middleware('permission:Export SW Waste Bins to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Waste Bins From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Waste Bin History', ['only' => ['history']]);
    }

    public function index()
    {
        $page_title = __('Waste Bins');
        $wards = Ward::getInAscOrder();
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');

        return view('swm.service-facilities.waste-bins.index', compact('page_title', 'wards', 'wasteBinTypes'));
    }

    public function getData(Request $request)
    {
        return $this->wasteBinService->getAll($request->all());
    }

    public function buildingSnapshot(Request $request): JsonResponse
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

        $roadCodeOut = ($roadCode !== '' && $roadCode !== '0') ? $roadCode : null;

        return response()->json([
            'ward' => $building->ward,
            'road_no' => $roadCodeOut,
            'road_name' => $roadName,
            'bin' => $building->bin,
        ]);
    }

    protected function bins(): array
    {
        return Building::query()->whereNull('deleted_at')->orderBy('bin')->pluck('bin', 'bin')->all();
    }

    public function create()
    {
        $page_title = __('Add Waste Bin');
        $wasteBin = null;
        $wards = Ward::getInAscOrder();
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $bins = $this->bins();

        return view('swm.service-facilities.waste-bins.create', compact(
            'page_title',
            'wasteBin',
            'wards',
            'wasteBinTypes',
            'bins'
        ));
    }

    public function store(WasteBinRequest $request)
    {
        $this->wasteBinService->storeOrUpdate(null, $request->validated());

        return redirect()->route('swm.waste-bins.index')->with('success', __('Waste bin created successfully.'));
    }

    public function show(WasteBin $wasteBin)
    {
        $wasteBin->load(['household', 'wasteBinType']);
        $page_title = __('Waste Bin Details');

        return view('swm.service-facilities.waste-bins.show', compact('page_title', 'wasteBin'));
    }

    public function edit(WasteBin $wasteBin)
    {
        $page_title = __('Edit Waste Bin');
        $wards = Ward::getInAscOrder();
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $bins = $this->bins();

        return view('swm.service-facilities.waste-bins.edit', compact(
            'page_title',
            'wasteBin',
            'wards',
            'wasteBinTypes',
            'bins'
        ));
    }

    public function update(WasteBinRequest $request, WasteBin $wasteBin)
    {
        $this->wasteBinService->storeOrUpdate($wasteBin, $request->validated());

        return redirect()->route('swm.waste-bins.index')->with('success', __('Waste bin updated successfully.'));
    }

    public function destroy(WasteBin $wasteBin)
    {
        $wasteBin->delete();

        return redirect()->route('swm.waste-bins.index')->with('success', __('Waste bin deleted successfully.'));
    }

    public function history(WasteBin $wasteBin)
    {
        $page_title = __('Waste Bin History');

        return view('swm.service-facilities.waste-bins.history', compact('page_title', 'wasteBin'));
    }

    public function export(Request $request)
    {
        $this->wasteBinService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->wasteBinService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Waste Bins'),
            route('swm.waste-bins.index'),
            'swm.waste-bins.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            request: $request,
            importClass: WasteBinImport::class,
            requiredHeaders: $this->wasteBinService->requiredImportLabels(),
            indexRoute: 'swm.waste-bins.index',
            disk: 'importswm',
            filenamePrefix: 'waste-bins',
            entityName: __('Waste Bins'),
        );
    }
}
