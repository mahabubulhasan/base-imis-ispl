<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WorkerRequest;
use App\Models\Swm\Organization;
use App\Models\Swm\WorkType;
use App\Models\Swm\Worker;
use App\Services\Swm\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerController extends Controller
{
    protected WorkerService $workerService;

    public function __construct(WorkerService $workerService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Workers', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SWM Worker', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Worker', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Worker', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Worker', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Workers to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Worker History', ['only' => ['history']]);
        $this->workerService = $workerService;
    }

    protected function organizationOptionsForForms(): array
    {
        $query = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name');
        if (Auth::user()->swm_organization_id) {
            $query->where('id', Auth::user()->swm_organization_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function workTypeOptionsForForms(): array
    {
        return WorkType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function workerBelongsToScopedOrg(?Worker $worker): bool
    {
        if (! $worker) {
            return false;
        }
        $oid = Auth::user()->swm_organization_id;

        return ! $oid || (int) $worker->organization_id === (int) $oid;
    }

    public function index()
    {
        $page_title = __('SWM Workers');
        $organizations = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name')->pluck('name', 'id');
        if (Auth::user()->swm_organization_id) {
            $organizations = Organization::query()->whereNull('deleted_at')->where('id', Auth::user()->swm_organization_id)->orderBy('name')->pluck('name', 'id');
        }
        $workTypes = WorkType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-providers.workers.index', compact('page_title', 'organizations', 'workTypes', 'scopedOrganizationId'));
    }

    public function getData(Request $request)
    {
        return $this->workerService->getAllWorkers($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM Worker');
        $worker = null;
        $organizations = $this->organizationOptionsForForms();
        $workTypes = $this->workTypeOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-providers.workers.create', compact('page_title', 'worker', 'organizations', 'workTypes', 'scopedOrganizationId'));
    }

    public function store(WorkerRequest $request)
    {
        $this->workerService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.workers.index')->with('success', __('SWM worker created successfully.'));
    }

    public function show($id)
    {
        $worker = Worker::with(['organization', 'workType'])->find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('SWM Worker Details');

            return view('swm.service-providers.workers.show', compact('page_title', 'worker'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('Edit SWM Worker');
            $organizations = $this->organizationOptionsForForms();
            $workTypes = $this->workTypeOptionsForForms();
            $scopedOrganizationId = Auth::user()->swm_organization_id;

            return view('swm.service-providers.workers.edit', compact('page_title', 'worker', 'organizations', 'workTypes', 'scopedOrganizationId'));
        }

        abort(404);
    }

    public function update(WorkerRequest $request, $id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $this->workerService->storeOrUpdate((int) $worker->id, $request->all());

            return redirect()->route('swm.workers.index')->with('success', __('SWM worker updated successfully.'));
        }

        return redirect()->route('swm.workers.index')->with('error', __('Failed to update SWM worker.'));
    }

    public function destroy($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $worker->delete();

            return redirect()->route('swm.workers.index')->with('success', __('SWM worker deleted successfully.'));
        }

        return redirect()->route('swm.workers.index')->with('error', __('Failed to delete SWM worker.'));
    }

    public function history($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('SWM Worker History');

            return view('swm.service-providers.workers.history', compact('page_title', 'worker'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->workerService->download($request->all());
    }
}
