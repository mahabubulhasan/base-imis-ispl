<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WorkTypeRequest;
use App\Models\Swm\WorkType;
use App\Services\Swm\WorkTypeService;
use Illuminate\Http\Request;

class WorkTypeController extends Controller
{
    protected WorkTypeService $workTypeService;

    public function __construct(WorkTypeService $workTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Work Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Work Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Work Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Work Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Work Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Work Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Work Type History', ['only' => ['history']]);
        $this->workTypeService = $workTypeService;
    }

    public function index()
    {
        $page_title = __('SW Work Types');

        return view('swm.service-providers.work-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->workTypeService->getAllWorkTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Work Type');
        $workType = null;

        return view('swm.service-providers.work-types.create', compact('page_title', 'workType'));
    }

    public function store(WorkTypeRequest $request)
    {
        $this->workTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.work-types.index')->with('success', __('SW work type created successfully.'));
    }

    public function show($id)
    {
        $workType = WorkType::find($id);
        if ($workType) {
            $page_title = __('SW Work Type Details');

            return view('swm.service-providers.work-types.show', compact('page_title', 'workType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $workType = WorkType::find($id);
        if ($workType) {
            $page_title = __('Edit SW Work Type');

            return view('swm.service-providers.work-types.edit', compact('page_title', 'workType'));
        }

        abort(404);
    }

    public function update(WorkTypeRequest $request, $id)
    {
        $workType = WorkType::find($id);
        if ($workType) {
            $this->workTypeService->storeOrUpdate((int) $workType->id, $request->all());

            return redirect()->route('swm.work-types.index')->with('success', __('SW work type updated successfully.'));
        }

        return redirect()->route('swm.work-types.index')->with('error', __('Failed to update SW work type.'));
    }

    public function destroy($id)
    {
        $workType = WorkType::find($id);
        if ($workType) {
            if ($workType->workers()->exists()) {
                return redirect()->route('swm.work-types.index')->with('error', __('Cannot delete SW work type that has associated workers.'));
            }
            $workType->delete();

            return redirect()->route('swm.work-types.index')->with('success', __('SW work type deleted successfully.'));
        }

        return redirect()->route('swm.work-types.index')->with('error', __('Failed to delete SW work type.'));
    }

    public function history($id)
    {
        $workType = WorkType::find($id);
        if ($workType) {
            $page_title = __('SW Work Type History');

            return view('swm.service-providers.work-types.history', compact('page_title', 'workType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->workTypeService->download($request->all());
    }
}
