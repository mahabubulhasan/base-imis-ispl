<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillRequest;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Services\Swm\LandfillService;
use Illuminate\Http\Request;

class LandfillController extends Controller
{
    protected LandfillService $landfillService;

    public function __construct(LandfillService $landfillService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Landfills', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SWM Landfill', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Landfill', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Landfill', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Landfill', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Landfills to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Landfill History', ['only' => ['history']]);
        $this->landfillService = $landfillService;
    }

    public function index()
    {
        $page_title = __('SWM Landfills');

        return view('swm.service-facilities.landfills.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->landfillService->getAllLandfills($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM Landfill');
        $landfill = null;

        return view('swm.service-facilities.landfills.create', compact('page_title', 'landfill'));
    }

    public function store(LandfillRequest $request)
    {
        $this->landfillService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.landfills.index')->with('success', __('SWM landfill created successfully.'));
    }

    public function show(Landfill $landfill)
    {
        $page_title = __('SWM Landfill Details');

        return view('swm.service-facilities.landfills.show', compact('page_title', 'landfill'));
    }

    public function edit(Landfill $landfill)
    {
        $page_title = __('Edit SWM Landfill');

        return view('swm.service-facilities.landfills.edit', compact('page_title', 'landfill'));
    }

    public function update(LandfillRequest $request, Landfill $landfill)
    {
        $this->landfillService->storeOrUpdate((int) $landfill->id, $request->all());

        return redirect()->route('swm.landfills.index')->with('success', __('SWM landfill updated successfully.'));
    }

    public function destroy(Landfill $landfill)
    {
        if (Sts::withTrashed()->where('destination_landfill_id', $landfill->id)->exists()) {
            return redirect()->route('swm.landfills.index')->with('error', __('Cannot delete SWM landfill that is set as destination for one or more STS records.'));
        }
        $landfill->delete();

        return redirect()->route('swm.landfills.index')->with('success', __('SWM landfill deleted successfully.'));
    }

    public function history(Landfill $landfill)
    {
        $page_title = __('SWM Landfill History');

        return view('swm.service-facilities.landfills.history', compact('page_title', 'landfill'));
    }

    public function export(Request $request)
    {
        return $this->landfillService->download($request->all());
    }
}
