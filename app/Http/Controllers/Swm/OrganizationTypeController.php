<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\OrganizationTypeRequest;
use App\Models\Swm\OrganizationType;
use App\Services\Swm\OrganizationTypeService;
use Illuminate\Http\Request;

class OrganizationTypeController extends Controller
{
    protected OrganizationTypeService $organizationTypeService;

    public function __construct(OrganizationTypeService $organizationTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Organization Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Organization Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Organization Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Organization Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Organization Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Organization Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Organization Type History', ['only' => ['history']]);
        $this->organizationTypeService = $organizationTypeService;
    }

    public function index()
    {
        $page_title = __('SW Organization Types');

        return view('swm.service-providers.organization-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->organizationTypeService->getAllOrganizationTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Organization Type');
        $organizationType = null;

        return view('swm.service-providers.organization-types.create', compact('page_title', 'organizationType'));
    }

    public function store(OrganizationTypeRequest $request)
    {
        $this->organizationTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.organization-types.index')->with('success', __('SW organization type created successfully.'));
    }

    public function show($id)
    {
        $organizationType = OrganizationType::find($id);
        if ($organizationType) {
            $page_title = __('SW Organization Type Details');

            return view('swm.service-providers.organization-types.show', compact('page_title', 'organizationType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $organizationType = OrganizationType::find($id);
        if ($organizationType) {
            $page_title = __('Edit SW Organization Type');

            return view('swm.service-providers.organization-types.edit', compact('page_title', 'organizationType'));
        }

        abort(404);
    }

    public function update(OrganizationTypeRequest $request, $id)
    {
        $organizationType = OrganizationType::find($id);
        if ($organizationType) {
            $this->organizationTypeService->storeOrUpdate((int) $organizationType->id, $request->all());

            return redirect()->route('swm.organization-types.index')->with('success', __('SW organization type updated successfully.'));
        }

        return redirect()->route('swm.organization-types.index')->with('error', __('Failed to update SW organization type.'));
    }

    public function destroy($id)
    {
        $organizationType = OrganizationType::find($id);
        if ($organizationType) {
            if ($organizationType->organizations()->exists()) {
                return redirect()->route('swm.organization-types.index')->with('error', __('Cannot delete SW organization type that has associated organizations.'));
            }
            $organizationType->delete();

            return redirect()->route('swm.organization-types.index')->with('success', __('SW organization type deleted successfully.'));
        }

        return redirect()->route('swm.organization-types.index')->with('error', __('Failed to delete SW organization type.'));
    }

    public function history($id)
    {
        $organizationType = OrganizationType::find($id);
        if ($organizationType) {
            $page_title = __('SW Organization Type History');

            return view('swm.service-providers.organization-types.history', compact('page_title', 'organizationType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->organizationTypeService->download($request->all());
    }
}
