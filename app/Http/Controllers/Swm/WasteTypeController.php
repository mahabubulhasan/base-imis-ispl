<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WasteTypeRequest;
use App\Models\Swm\Sts;
use App\Models\Swm\WasteType;
use App\Services\Swm\WasteTypeService;
use Illuminate\Http\Request;

class WasteTypeController extends Controller
{
    protected WasteTypeService $wasteTypeService;

    public function __construct(WasteTypeService $wasteTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Waste Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Waste Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Waste Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Waste Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Waste Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Waste Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Waste Type History', ['only' => ['history']]);
        $this->wasteTypeService = $wasteTypeService;
    }

    public function index()
    {
        $page_title = __('SW Waste Types');

        return view('swm.service-providers.waste-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->wasteTypeService->getAllWasteTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Waste Type');
        $wasteType = null;

        return view('swm.service-providers.waste-types.create', compact('page_title', 'wasteType'));
    }

    public function store(WasteTypeRequest $request)
    {
        $this->wasteTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.waste-types.index')->with('success', __('SW waste type created successfully.'));
    }

    public function show($id)
    {
        $wasteType = WasteType::find($id);
        if ($wasteType) {
            $page_title = __('SW Waste Type Details');

            return view('swm.service-providers.waste-types.show', compact('page_title', 'wasteType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $wasteType = WasteType::find($id);
        if ($wasteType) {
            $page_title = __('Edit SW Waste Type');

            return view('swm.service-providers.waste-types.edit', compact('page_title', 'wasteType'));
        }

        abort(404);
    }

    public function update(WasteTypeRequest $request, $id)
    {
        $wasteType = WasteType::find($id);
        if ($wasteType) {
            $this->wasteTypeService->storeOrUpdate((int) $wasteType->id, $request->all());

            return redirect()->route('swm.waste-types.index')->with('success', __('SW waste type updated successfully.'));
        }

        return redirect()->route('swm.waste-types.index')->with('error', __('Failed to update SW waste type.'));
    }

    public function destroy($id)
    {
        $wasteType = WasteType::find($id);
        if ($wasteType) {
            if (Sts::query()->whereJsonContains('waste_type_ids', (int) $wasteType->id)->exists()) {
                return redirect()->route('swm.waste-types.index')->with('error', __('Cannot delete SW waste type that is referenced by one or more SW STS.'));
            }
            $wasteType->delete();

            return redirect()->route('swm.waste-types.index')->with('success', __('SW waste type deleted successfully.'));
        }

        return redirect()->route('swm.waste-types.index')->with('error', __('Failed to delete SW waste type.'));
    }

    public function history($id)
    {
        $wasteType = WasteType::find($id);
        if ($wasteType) {
            $page_title = __('SW Waste Type History');

            return view('swm.service-providers.waste-types.history', compact('page_title', 'wasteType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->wasteTypeService->download($request->all());
    }
}
