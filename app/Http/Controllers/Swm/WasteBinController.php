<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WasteBinRequest;
use App\Models\BuildingInfo\Household;
use App\Models\Swm\WasteBin;
use App\Services\Swm\WasteBinService;
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
        $households = Household::query()->whereNull('deleted_at')->orderBy('household_id')->pluck('household_id', 'id');
        return view('swm.service-facilities.waste-bins.index', compact('page_title', 'households'));
    }

    public function getData(Request $request)
    {
        return $this->wasteBinService->getAll($request->all());
    }

    public function create()
    {
        $page_title = __('Add Waste Bin');
        $wasteBin = null;
        $households = Household::query()->whereNull('deleted_at')->orderBy('household_id')->pluck('household_id', 'id');
        return view('swm.service-facilities.waste-bins.create', compact('page_title', 'wasteBin', 'households'));
    }

    public function store(WasteBinRequest $request)
    {
        $this->wasteBinService->storeOrUpdate(null, $request->validated());
        return redirect()->route('swm.waste-bins.index')->with('success', __('Waste bin created successfully.'));
    }

    public function show(WasteBin $wasteBin)
    {
        $page_title = __('Waste Bin Details');
        return view('swm.service-facilities.waste-bins.show', compact('page_title', 'wasteBin'));
    }

    public function edit(WasteBin $wasteBin)
    {
        $page_title = __('Edit Waste Bin');
        $households = Household::query()->whereNull('deleted_at')->orderBy('household_id')->pluck('household_id', 'id');
        return view('swm.service-facilities.waste-bins.edit', compact('page_title', 'wasteBin', 'households'));
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
