<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\StsRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Models\UtilityInfo\Roadline;
use App\Services\Swm\StsService;
use Illuminate\Http\Request;

class StsController extends Controller
{
    protected StsService $stsService;

    public function __construct(StsService $stsService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW STS', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW STS', ['only' => ['show']]);
        $this->middleware('permission:Add SW STS', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW STS', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW STS', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW STS to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW STS History', ['only' => ['history']]);
        $this->stsService = $stsService;
    }

    protected function landfillOptions(): array
    {
        return Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function (Landfill $landfill) {
                $label = trim(($landfill->landfill_id ? $landfill->landfill_id.' - ' : '').$landfill->name);

                return [$landfill->id => $label];
            })
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

    public function index()
    {
        $page_title = __('SW STS');
        $landfills = $this->landfillOptions();
        $wards = $this->wardOptions();
        $wasteTypes = $this->wasteTypeOptions();

        return view('swm.service-facilities.sts.index', compact('page_title', 'landfills', 'wards', 'wasteTypes'));
    }

    public function getData(Request $request)
    {
        return $this->stsService->getAllSts($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW STS');
        $sts = null;
        $landfills = $this->landfillOptions();
        $wards = $this->wardOptions();
        $wasteTypes = $this->wasteTypeOptions();

        return view('swm.service-facilities.sts.create', compact('page_title', 'sts', 'landfills', 'wards', 'wasteTypes'));
    }

    public function store(StsRequest $request)
    {
        $this->stsService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.sts.index')->with('success', __('SW STS created successfully.'));
    }

    public function show(Sts $sts)
    {
        $sts->load('landfill');
        $page_title = __('SW STS Details');
        $wasteTypes = $sts->wasteTypes();

        return view('swm.service-facilities.sts.show', compact('page_title', 'sts', 'wasteTypes'));
    }

    public function edit(Sts $sts)
    {
        $page_title = __('Edit SW STS');
        $landfills = $this->landfillOptions();
        $wards = $this->wardOptions();
        $wasteTypes = $this->wasteTypeOptions();

        return view('swm.service-facilities.sts.edit', compact('page_title', 'sts', 'landfills', 'wards', 'wasteTypes'));
    }

    public function update(StsRequest $request, Sts $sts)
    {
        $this->stsService->storeOrUpdate((int) $sts->id, $request->all());

        return redirect()->route('swm.sts.index')->with('success', __('SW STS updated successfully.'));
    }

    public function destroy(Sts $sts)
    {
        if (Vehicle::query()->where('dumping_sts_id', $sts->id)->exists()) {
            return redirect()->route('swm.sts.index')->with('error', __('Cannot delete SW STS that is set as dumping place for one or more vehicles.'));
        }
        $sts->delete();

        return redirect()->route('swm.sts.index')->with('success', __('SW STS deleted successfully.'));
    }

    public function history(Sts $sts)
    {
        $page_title = __('SW STS History');

        return view('swm.service-facilities.sts.history', compact('page_title', 'sts'));
    }

    public function export(Request $request)
    {
        return $this->stsService->download($request->all());
    }

    /**
     * Return roads in the given ward for autopopulating road_name when road_id is selected.
     * Response: [{ code, name }, ...]
     */
    public function roadsForWard(Request $request)
    {
        $ward = $request->input('ward_no');

        $query = Roadline::query()
            ->whereNull('deleted_at')
            ->select(['code', 'name']);

        if ($ward !== null && $ward !== '') {
            $query->where('ward', (int) $ward);
        }

        return response()->json(
            $query->orderBy('name')->limit(2000)->get()
        );
    }
}
