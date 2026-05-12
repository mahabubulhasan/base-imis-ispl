<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillTypeRequest;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillType;
use App\Services\Swm\LandfillTypeService;
use Illuminate\Http\Request;

class LandfillTypeController extends Controller
{
    protected LandfillTypeService $landfillTypeService;

    public function __construct(LandfillTypeService $landfillTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Landfill Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Landfill Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Landfill Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Landfill Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Landfill Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Landfill Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Landfill Type History', ['only' => ['history']]);
        $this->landfillTypeService = $landfillTypeService;
    }

    public function index()
    {
        $page_title = __('SW Landfill Types');

        return view('swm.service-providers.landfill-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->landfillTypeService->getAllLandfillTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Landfill Type');
        $landfillType = null;

        return view('swm.service-providers.landfill-types.create', compact('page_title', 'landfillType'));
    }

    public function store(LandfillTypeRequest $request)
    {
        $this->landfillTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.landfill-types.index')->with('success', __('SW landfill type created successfully.'));
    }

    public function show($id)
    {
        $landfillType = LandfillType::find($id);
        if ($landfillType) {
            $page_title = __('SW Landfill Type Details');

            return view('swm.service-providers.landfill-types.show', compact('page_title', 'landfillType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $landfillType = LandfillType::find($id);
        if ($landfillType) {
            $page_title = __('Edit SW Landfill Type');

            return view('swm.service-providers.landfill-types.edit', compact('page_title', 'landfillType'));
        }

        abort(404);
    }

    public function update(LandfillTypeRequest $request, $id)
    {
        $landfillType = LandfillType::find($id);
        if ($landfillType) {
            $this->landfillTypeService->storeOrUpdate((int) $landfillType->id, $request->all());

            return redirect()->route('swm.landfill-types.index')->with('success', __('SW landfill type updated successfully.'));
        }

        return redirect()->route('swm.landfill-types.index')->with('error', __('Failed to update SW landfill type.'));
    }

    public function destroy($id)
    {
        $landfillType = LandfillType::find($id);
        if ($landfillType) {
            if (Landfill::query()->where('landfill_type_id', (int) $landfillType->id)->exists()) {
                return redirect()->route('swm.landfill-types.index')->with('error', __('Cannot delete SW landfill type that is referenced by one or more SW landfills.'));
            }
            $landfillType->delete();

            return redirect()->route('swm.landfill-types.index')->with('success', __('SW landfill type deleted successfully.'));
        }

        return redirect()->route('swm.landfill-types.index')->with('error', __('Failed to delete SW landfill type.'));
    }

    public function history($id)
    {
        $landfillType = LandfillType::find($id);
        if ($landfillType) {
            $page_title = __('SW Landfill Type History');

            return view('swm.service-providers.landfill-types.history', compact('page_title', 'landfillType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->landfillTypeService->download($request->all());
    }
}
