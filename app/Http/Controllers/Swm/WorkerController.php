<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\WorkerRequest;
use App\Imports\Swm\WorkerImport;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Organization;
use App\Models\Swm\WorkType;
use App\Models\Swm\Worker;
use App\Services\Swm\WorkerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    use HandlesSwmExcelImport;

    protected WorkerService $workerService;

    public function __construct(WorkerService $workerService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Workers', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Worker', ['only' => ['show']]);
        $this->middleware('permission:Add SW Worker', ['only' => ['create', 'store', 'nextWorkerId']]);
        $this->middleware('permission:Edit SW Worker', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Worker', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Workers to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Workers From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Worker History', ['only' => ['history']]);
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

    protected function wardOptionsForForms(): array
    {
        return Ward::getInAscOrder();
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
        $page_title = __('Workers');
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
        $page_title = __('Add Worker');
        $worker = null;
        $organizations = $this->organizationOptionsForForms();
        $workTypes = $this->workTypeOptionsForForms();
        $wards = $this->wardOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-providers.workers.create', compact('page_title', 'worker', 'organizations', 'workTypes', 'wards', 'scopedOrganizationId'));
    }

    public function nextWorkerId(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => [
                'required',
                'integer',
                Rule::exists('pgsql.swm.organizations', 'id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
        ]);

        $orgId = (int) $validated['organization_id'];
        $scoped = Auth::user()->swm_organization_id;
        if ($scoped && (int) $scoped !== $orgId) {
            abort(403);
        }

        return response()->json([
            'worker_id_no' => $this->workerService->peekNextWorkerIdNo($orgId),
        ]);
    }

    public function store(WorkerRequest $request)
    {
        $this->workerService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.workers.index')->with('success', __('Worker created successfully.'));
    }

    public function show($id)
    {
        $worker = Worker::with(['organization', 'workType'])->find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('Worker Details');
            $wards = $this->wardOptionsForForms();

            return view('swm.service-providers.workers.show', compact('page_title', 'worker', 'wards'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('Edit Worker');
            $organizations = $this->organizationOptionsForForms();
            $workTypes = $this->workTypeOptionsForForms();
            $wards = $this->wardOptionsForForms();
            $scopedOrganizationId = Auth::user()->swm_organization_id;

            return view('swm.service-providers.workers.edit', compact('page_title', 'worker', 'organizations', 'workTypes', 'wards', 'scopedOrganizationId'));
        }

        abort(404);
    }

    public function update(WorkerRequest $request, $id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $this->workerService->storeOrUpdate((int) $worker->id, $request->all());

            return redirect()->route('swm.workers.index')->with('success', __('Worker updated successfully.'));
        }

        return redirect()->route('swm.workers.index')->with('error', __('Failed to update worker.'));
    }

    public function destroy($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            if ($worker->vehiclesAsDriver()->exists()) {
                return redirect()->route('swm.workers.index')->with('error', __('Cannot delete worker that is assigned as driver on one or more vehicles.'));
            }
            $worker->delete();

            return redirect()->route('swm.workers.index')->with('success', __('Worker deleted successfully.'));
        }

        return redirect()->route('swm.workers.index')->with('error', __('Failed to delete worker.'));
    }

    public function history($id)
    {
        $worker = Worker::find($id);
        if ($worker && $this->workerBelongsToScopedOrg($worker)) {
            $page_title = __('Worker History');

            return view('swm.service-providers.workers.history', compact('page_title', 'worker'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->workerService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->workerService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Workers'),
            route('swm.workers.index'),
            'swm.workers.import.store'
        );
    }

    public function importStore(Request $request)
    {
        $required = ['work_type', 'name', 'mobile'];
        if (! Auth::user()->swm_organization_id) {
            $required[] = 'organization';
        }

        return $this->swmImportStore(
            $request,
            WorkerImport::class,
            $required,
            'swm.workers.index',
            'importswm',
            'workers'
        );
    }
}
