<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillRequest;
use App\Models\Swm\Landfill;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
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

    public function index()
    {
        $page_title = __('SW Landfills');

        return view('swm.service-facilities.landfills.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->landfillService->getAllLandfills($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Landfill');
        $landfill = null;

        return view('swm.service-facilities.landfills.create', compact('page_title', 'landfill'));
    }

    public function store(LandfillRequest $request)
    {
        $this->landfillService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.landfills.index')->with('success', __('SW landfill created successfully.'));
    }

    public function show(Landfill $landfill)
    {
        $page_title = __('SW Landfill Details');

        return view('swm.service-facilities.landfills.show', compact('page_title', 'landfill'));
    }

    public function edit(Landfill $landfill)
    {
        $page_title = __('Edit SW Landfill');

        return view('swm.service-facilities.landfills.edit', compact('page_title', 'landfill'));
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
        $page_title = __('SW Landfill History');

        return view('swm.service-facilities.landfills.history', compact('page_title', 'landfill'));
    }

    public function export(Request $request)
    {
        return $this->landfillService->download($request->all());
    }
}
