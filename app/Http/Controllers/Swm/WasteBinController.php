<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WasteBinRequest;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\WasteBin;
use App\Models\Swm\WasteBinType;
use App\Services\Swm\WasteBinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WasteBinController extends Controller
{
    public function __construct(protected WasteBinService $wasteBinService)
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $page_title = __('Waste Bins');

        return view('swm.service-facilities.waste-bins.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->wasteBinService->getAll($request->all());
    }

    public function householdFields(Request $request): JsonResponse
    {
        $id = $request->query('household_id');
        if (! $id) {
            return response()->json([]);
        }

        $household = Household::query()->whereNull('deleted_at')->with('building')->find($id);
        if (! $household) {
            return response()->json(null, 404);
        }

        $fallbackRoadNo = trim((string) ($household->building?->road_code ?? ''));
        $roadNo = $household->road_no ?? ($fallbackRoadNo !== '' && $fallbackRoadNo !== '0' ? $fallbackRoadNo : null);

        return response()->json([
            'sub_location' => $household->sub_location,
            'ward_no' => $household->ward,
            'road_name' => $household->road_name,
            'road_no' => $roadNo,
            'bin' => $household->bin,
        ]);
    }

    public function create()
    {
        $page_title = __('Add Waste Bin');
        $wasteBin = null;
        $households = Household::query()->whereNull('deleted_at')->activeStatus()->orderBy('household_id')->pluck('household_id', 'id');
        $wards = Ward::getInAscOrder();
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $othersWasteBinTypeId = WasteBinType::query()
            ->where('name', WasteBinType::OTHERS_SPECIFY_NAME)
            ->whereNull('deleted_at')
            ->value('id');

        return view('swm.service-facilities.waste-bins.create', compact(
            'page_title',
            'wasteBin',
            'households',
            'wards',
            'wasteBinTypes',
            'othersWasteBinTypeId'
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
        $households = Household::query()
            ->whereNull('deleted_at')
            ->where(function ($q) use ($wasteBin) {
                $q->activeStatus()
                    ->orWhere('id', $wasteBin->household_id);
            })
            ->orderBy('household_id')
            ->pluck('household_id', 'id');
        $wards = Ward::getInAscOrder();
        $wasteBinTypes = WasteBinType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $othersWasteBinTypeId = WasteBinType::query()
            ->where('name', WasteBinType::OTHERS_SPECIFY_NAME)
            ->whereNull('deleted_at')
            ->value('id');

        return view('swm.service-facilities.waste-bins.edit', compact(
            'page_title',
            'wasteBin',
            'households',
            'wards',
            'wasteBinTypes',
            'othersWasteBinTypeId'
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
}
