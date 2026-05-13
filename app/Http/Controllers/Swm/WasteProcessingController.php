<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\WasteProcessingRequest;
use App\Models\Swm\Organization;
use App\Models\Swm\WasteProcessingLog;
use App\Services\Swm\WasteProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WasteProcessingController extends Controller
{
    public function __construct(
        protected WasteProcessingService $wasteProcessingService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Waste Processing', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Waste Processing', ['only' => ['show']]);
        $this->middleware('permission:Add SW Waste Processing', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Waste Processing', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Waste Processing', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Waste Processing to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Waste Processing History', ['only' => ['history']]);
    }

    protected function organizationOptionsForForms(): array
    {
        $query = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name');
        if (Auth::user()->swm_organization_id) {
            $query->where('id', Auth::user()->swm_organization_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function logBelongsToScopedOrg(?WasteProcessingLog $log): bool
    {
        if (! $log) {
            return false;
        }
        $oid = Auth::user()->swm_organization_id;

        return ! $oid || (int) $log->organization_id === (int) $oid;
    }

    public function index()
    {
        $page_title = __('Waste Processing');
        $organizations = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name')->pluck('name', 'id');
        if (Auth::user()->swm_organization_id) {
            $organizations = Organization::query()->whereNull('deleted_at')->where('id', Auth::user()->swm_organization_id)->orderBy('name')->pluck('name', 'id');
        }
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-management.waste-processing.index', compact(
            'page_title',
            'organizations',
            'scopedOrganizationId'
        ));
    }

    public function getData(Request $request)
    {
        return $this->wasteProcessingService->getAll($request->all());
    }

    public function create()
    {
        $page_title = __('Add Waste Processing');
        $wasteProcessingLog = null;
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-management.waste-processing.create', compact(
            'page_title',
            'wasteProcessingLog',
            'organizations',
            'scopedOrganizationId'
        ));
    }

    public function store(WasteProcessingRequest $request)
    {
        $id = $this->wasteProcessingService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Failed to create waste processing log.'));
        }

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log created successfully.'));
    }

    public function show(WasteProcessingLog $waste_processing)
    {
        if (! $this->logBelongsToScopedOrg($waste_processing)) {
            abort(403);
        }
        $page_title = __('Waste Processing Details');
        $wasteProcessingLog = $waste_processing->load('organization');

        return view('swm.service-management.waste-processing.show', compact('page_title', 'wasteProcessingLog'));
    }

    public function edit(WasteProcessingLog $waste_processing)
    {
        if (! $this->logBelongsToScopedOrg($waste_processing)) {
            abort(403);
        }
        $page_title = __('Edit Waste Processing');
        $wasteProcessingLog = $waste_processing->load('organization');
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;

        return view('swm.service-management.waste-processing.edit', compact(
            'page_title',
            'wasteProcessingLog',
            'organizations',
            'scopedOrganizationId'
        ));
    }

    public function update(WasteProcessingRequest $request, WasteProcessingLog $waste_processing)
    {
        if (! $this->logBelongsToScopedOrg($waste_processing)) {
            abort(403);
        }

        $id = $this->wasteProcessingService->storeOrUpdate((int) $waste_processing->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Failed to update waste processing log.'));
        }

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log updated successfully.'));
    }

    public function destroy(WasteProcessingLog $waste_processing)
    {
        if (! $this->logBelongsToScopedOrg($waste_processing)) {
            abort(403);
        }
        $waste_processing->delete();

        return redirect()->route('swm.waste-processing.index')->with('success', __('Waste processing log deleted successfully.'));
    }

    public function history(WasteProcessingLog $waste_processing)
    {
        if (! $this->logBelongsToScopedOrg($waste_processing)) {
            abort(403);
        }
        $page_title = __('Waste Processing History');
        $wasteProcessingLog = $waste_processing;

        return view('swm.service-management.waste-processing.history', compact('page_title', 'wasteProcessingLog'));
    }

    public function export(Request $request)
    {
        return $this->wasteProcessingService->download($request->all());
    }
}
