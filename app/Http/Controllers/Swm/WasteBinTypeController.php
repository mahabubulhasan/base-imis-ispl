<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WasteBinTypeRequest;
use App\Models\Swm\WasteBinType;
use App\Services\Swm\WasteBinTypeService;
use Illuminate\Http\Request;

class WasteBinTypeController extends Controller
{
    public function __construct(protected WasteBinTypeService $wasteBinTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Waste Bin Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Waste Bin Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Waste Bin Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Waste Bin Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Waste Bin Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Waste Bin Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Waste Bin Type History', ['only' => ['history']]);
    }

    public function index()
    {
        $page_title = __('SW Waste Bin Types');

        return view('swm.service-providers.waste-bin-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->wasteBinTypeService->getAllWasteBinTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Waste Bin Type');
        $wasteBinType = null;

        return view('swm.service-providers.waste-bin-types.create', compact('page_title', 'wasteBinType'));
    }

    public function store(WasteBinTypeRequest $request)
    {
        $this->wasteBinTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.waste-bin-types.index')->with('success', __('SW waste bin type created successfully.'));
    }

    public function show($id)
    {
        $wasteBinType = WasteBinType::find($id);
        if ($wasteBinType) {
            $page_title = __('SW Waste Bin Type Details');

            return view('swm.service-providers.waste-bin-types.show', compact('page_title', 'wasteBinType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $wasteBinType = WasteBinType::find($id);
        if ($wasteBinType) {
            $page_title = __('Edit SW Waste Bin Type');

            return view('swm.service-providers.waste-bin-types.edit', compact('page_title', 'wasteBinType'));
        }

        abort(404);
    }

    public function update(WasteBinTypeRequest $request, $id)
    {
        $wasteBinType = WasteBinType::find($id);
        if ($wasteBinType) {
            $this->wasteBinTypeService->storeOrUpdate((int) $wasteBinType->id, $request->all());

            return redirect()->route('swm.waste-bin-types.index')->with('success', __('SW waste bin type updated successfully.'));
        }

        return redirect()->route('swm.waste-bin-types.index')->with('error', __('Failed to update SW waste bin type.'));
    }

    public function destroy($id)
    {
        $wasteBinType = WasteBinType::find($id);
        if ($wasteBinType) {
            if ($wasteBinType->wasteBins()->exists()) {
                return redirect()->route('swm.waste-bin-types.index')->with('error', __('Cannot delete SW waste bin type that has associated waste bins.'));
            }
            $wasteBinType->delete();

            return redirect()->route('swm.waste-bin-types.index')->with('success', __('SW waste bin type deleted successfully.'));
        }

        return redirect()->route('swm.waste-bin-types.index')->with('error', __('Failed to delete SW waste bin type.'));
    }

    public function history($id)
    {
        $wasteBinType = WasteBinType::find($id);
        if ($wasteBinType) {
            $page_title = __('SW Waste Bin Type History');

            return view('swm.service-providers.waste-bin-types.history', compact('page_title', 'wasteBinType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->wasteBinTypeService->download($request->all());
    }
}
