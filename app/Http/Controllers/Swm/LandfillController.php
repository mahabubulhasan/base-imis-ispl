<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillType;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Services\Swm\LandfillService;
use Illuminate\Http\Request;

class LandfillController extends Controller
{
    protected LandfillService $landfillService;

    public function __construct(LandfillService $landfillService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Landfills', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Landfill', ['only' => ['show']]);
        $this->middleware('permission:Add SW Landfill', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Landfill', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Landfill', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Landfills to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Landfill History', ['only' => ['history']]);
        $this->landfillService = $landfillService;
    }

    protected function stsOptions(): array
    {
        return Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Sts $sts) {
                $label = trim(($sts->sts_id ? $sts->sts_id.' - ' : '').$sts->name);

                return [$sts->id => $label];
            })
            ->all();
    }

    protected function stsWardMap(): array
    {
        return Sts::query()
            ->whereNull('deleted_at')
            ->whereNotNull('ward_no')
            ->pluck('ward_no', 'id')
            ->map(fn ($ward) => (int) $ward)
            ->all();
    }

    protected function wardOptions(): array
    {
        return Ward::getInAscOrder();
    }

    protected function wasteTypeOptions(): array
    {
        return WasteType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function landfillTypeOptions(): array
    {
        return LandfillType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    public function index()
    {
        $page_title = __('Landfills');
        $stsOptions = $this->stsOptions();
        $wasteTypes = $this->wasteTypeOptions();
        $landfillTypes = $this->landfillTypeOptions();

        return view('swm.service-facilities.landfills.index', compact('page_title', 'wasteTypes', 'stsOptions', 'landfillTypes'));
    }

    public function getData(Request $request)
    {
        return $this->landfillService->getAllLandfills($request->all());
    }

    public function create()
    {
        $page_title = __('Add Landfill');
        $landfill = null;
        $stsOptions = $this->stsOptions();
        $stsWardMap = $this->stsWardMap();
        $wards = $this->wardOptions();
        $wasteTypes = $this->wasteTypeOptions();
        $landfillTypes = $this->landfillTypeOptions();

        return view('swm.service-facilities.landfills.create', compact('page_title', 'landfill', 'stsOptions', 'stsWardMap', 'wards', 'wasteTypes', 'landfillTypes'));
    }

    public function store(LandfillRequest $request)
    {
        $this->landfillService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.landfills.index')->with('success', __('SW landfill created successfully.'));
    }

    public function show(Landfill $landfill)
    {
        $page_title = __('Landfill Details');
        $sourceSts = $landfill->sourceSts();
        $wasteTypes = $landfill->wasteTypes();
        $landfill->load('landfillType');

        return view('swm.service-facilities.landfills.show', compact('page_title', 'landfill', 'sourceSts', 'wasteTypes'));
    }

    public function edit(Landfill $landfill)
    {
        $page_title = __('Edit Landfill');
        $stsOptions = $this->stsOptions();
        $stsWardMap = $this->stsWardMap();
        $wards = $this->wardOptions();
        $wasteTypes = $this->wasteTypeOptions();
        $landfillTypes = $this->landfillTypeOptions();

        return view('swm.service-facilities.landfills.edit', compact('page_title', 'landfill', 'stsOptions', 'stsWardMap', 'wards', 'wasteTypes', 'landfillTypes'));
    }

    public function update(LandfillRequest $request, Landfill $landfill)
    {
        $this->landfillService->storeOrUpdate((int) $landfill->id, $request->all());

        return redirect()->route('swm.landfills.index')->with('success', __('SW landfill updated successfully.'));
    }

    public function destroy(Landfill $landfill)
    {
        if (Sts::withTrashed()->where('destination_landfill_id', $landfill->id)->exists()) {
            return redirect()->route('swm.landfills.index')->with('error', __('Cannot delete SW landfill that is set as destination for one or more STS records.'));
        }
        if (Vehicle::query()->where('dumping_landfill_id', $landfill->id)->exists()) {
            return redirect()->route('swm.landfills.index')->with('error', __('Cannot delete SW landfill that is set as dumping place for one or more vehicles.'));
        }
        $landfill->delete();

        return redirect()->route('swm.landfills.index')->with('success', __('SW landfill deleted successfully.'));
    }

    public function history(Landfill $landfill)
    {
        $page_title = __('Landfill History');

        return view('swm.service-facilities.landfills.history', compact('page_title', 'landfill'));
    }

    /**
     * Return ward numbers derived from selected STS ids.
     * Response: [ward_no, ...]
     */
    public function wardsForSts(Request $request)
    {
        $ids = $request->input('source_sts_ids', []);
        if (! is_array($ids)) {
            $ids = [$ids];
        }
        $ids = array_values(array_filter(array_map('intval', $ids), fn ($id) => $id > 0));
        if (empty($ids)) {
            return response()->json([]);
        }

        $wards = Sts::query()
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->whereNotNull('ward_no')
            ->pluck('ward_no')
            ->map(fn ($w) => (int) $w)
            ->unique()
            ->sort()
            ->values()
            ->all();

        return response()->json($wards);
    }

    public function export(Request $request)
    {
        return $this->landfillService->download($request->all());
    }
}
