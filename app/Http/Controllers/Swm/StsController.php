<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\StsRequest;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Services\Swm\StsService;
use Illuminate\Http\Request;

class StsController extends Controller
{
    protected StsService $stsService;

    public function __construct(StsService $stsService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM STS', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SWM STS', ['only' => ['show']]);
        $this->middleware('permission:Add SWM STS', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM STS', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM STS', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM STS to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM STS History', ['only' => ['history']]);
        $this->stsService = $stsService;
    }

    protected function landfillOptions(): array
    {
        return Landfill::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    public function index()
    {
        $page_title = __('SWM STS');
        $landfills = $this->landfillOptions();

        return view('swm.service-facilities.sts.index', compact('page_title', 'landfills'));
    }

    public function getData(Request $request)
    {
        return $this->stsService->getAllSts($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM STS');
        $sts = null;
        $landfills = $this->landfillOptions();

        return view('swm.service-facilities.sts.create', compact('page_title', 'sts', 'landfills'));
    }

    public function store(StsRequest $request)
    {
        $this->stsService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.sts.index')->with('success', __('SWM STS created successfully.'));
    }

    public function show(Sts $sts)
    {
        $sts->load('landfill');
        $page_title = __('SWM STS Details');

        return view('swm.service-facilities.sts.show', compact('page_title', 'sts'));
    }

    public function edit(Sts $sts)
    {
        $page_title = __('Edit SWM STS');
        $landfills = $this->landfillOptions();

        return view('swm.service-facilities.sts.edit', compact('page_title', 'sts', 'landfills'));
    }

    public function update(StsRequest $request, Sts $sts)
    {
        $this->stsService->storeOrUpdate((int) $sts->id, $request->all());

        return redirect()->route('swm.sts.index')->with('success', __('SWM STS updated successfully.'));
    }

    public function destroy(Sts $sts)
    {
        if (Vehicle::query()->where('dumping_sts_id', $sts->id)->exists()) {
            return redirect()->route('swm.sts.index')->with('error', __('Cannot delete SWM STS that is set as dumping place for one or more vehicles.'));
        }
        $sts->delete();

        return redirect()->route('swm.sts.index')->with('success', __('SWM STS deleted successfully.'));
    }

    public function history(Sts $sts)
    {
        $page_title = __('SWM STS History');

        return view('swm.service-facilities.sts.history', compact('page_title', 'sts'));
    }

    public function export(Request $request)
    {
        return $this->stsService->download($request->all());
    }
}
