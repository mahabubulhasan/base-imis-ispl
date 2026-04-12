<?php

namespace App\Http\Controllers\Swm;

use App\Enums\SwmOrganizationStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\OrganizationRequest;
use App\Models\Swm\Organization;
use App\Services\Auth\UserService;
use App\Services\Swm\OrganizationService;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    protected OrganizationService $organizationService;

    protected UserService $userService;

    public function __construct(OrganizationService $organizationService, UserService $userService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Organizations', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SWM Organization', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Organization', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Organization', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Organization', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Organizations to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Organization History', ['only' => ['history']]);
        $this->organizationService = $organizationService;
        $this->userService = $userService;
    }

    public function index()
    {
        $page_title = __('SWM Organizations');
        $organizationStatus = SwmOrganizationStatus::asSelectArray();

        return view('swm.service-providers.organizations.index', compact('page_title', 'organizationStatus'));
    }

    public function getData(Request $request)
    {
        return $this->organizationService->getAllOrganizations($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM Organization');
        $organization = null;
        $organizationStatus = SwmOrganizationStatus::asSelectArray();

        return view('swm.service-providers.organizations.create', compact('page_title', 'organization', 'organizationStatus'));
    }

    public function store(OrganizationRequest $request)
    {
        $data = $request->all();
        $organizationId = $this->organizationService->storeOrUpdate(null, $data);

        if ($request->has('create_user')) {
            $data['swm_organization_id'] = $organizationId;
            $data['user_type'] = 'SWM Organization';
            $data['roles'] = 'SWM Organization - Admin';
            $data['gender'] = 'Male';
            $data['username'] = explode('@', (string) $request->email)[0];
            $data['status'] = UserStatus::Active;
            $this->userService->storeOrUpdate(null, $data);
            $successMessage = __('SWM organization and SWM organization admin user created successfully.');
        } else {
            $successMessage = __('SWM organization created successfully.');
        }

        return redirect()->route('swm.organizations.index')->with('success', $successMessage);
    }

    public function show($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $page_title = __('SWM Organization Details');
            $status = SwmOrganizationStatus::getDescription($organization->status);

            return view('swm.service-providers.organizations.show', compact('page_title', 'organization', 'status'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $organization = Organization::find($id);
        $organizationStatus = SwmOrganizationStatus::asSelectArray();
        if ($organization) {
            $page_title = __('Edit SWM Organization');

            return view('swm.service-providers.organizations.edit', compact('page_title', 'organization', 'organizationStatus'));
        }

        abort(404);
    }

    public function update(OrganizationRequest $request, $id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $this->organizationService->storeOrUpdate((int) $organization->id, $request->all());

            return redirect()->route('swm.organizations.index')->with('success', __('SWM organization updated successfully.'));
        }

        return redirect()->route('swm.organizations.index')->with('error', __('Failed to update SWM organization.'));
    }

    public function destroy($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            if ($organization->users()->exists()) {
                return redirect()->route('swm.organizations.index')->with('error', __('Cannot delete SWM organization that has associated user information.'));
            }
            $organization->delete();

            return redirect()->route('swm.organizations.index')->with('success', __('SWM organization deleted successfully.'));
        }

        return redirect()->route('swm.organizations.index')->with('error', __('Failed to delete SWM organization.'));
    }

    public function history($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $page_title = __('SWM Organization History');

            return view('swm.service-providers.organizations.history', compact('page_title', 'organization'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->organizationService->download($request->all());
    }
}
