<?php

namespace App\Http\Controllers\Swm;

use App\Enums\SwmOrganizationStatus;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\OrganizationRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Organization;
use App\Models\Swm\OrganizationType;
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
        $this->middleware('permission:List SW Organizations', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Organization', ['only' => ['show']]);
        $this->middleware('permission:Add SW Organization', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Organization', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Organization', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Organizations to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Organization History', ['only' => ['history']]);
        $this->organizationService = $organizationService;
        $this->userService = $userService;
    }

    protected function wardOptions(): array
    {
        return Ward::getInAscOrder();
    }

    protected function organizationTypeOptions(): array
    {
        return OrganizationType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (OrganizationType $type) => [$type->id => $type->name])
            ->all();
    }

    public function index()
    {
        $page_title = __('Organizations');
        $organizationStatus = SwmOrganizationStatus::asSelectArray();
        $organizationTypes = $this->organizationTypeOptions();

        return view('swm.service-providers.organizations.index', compact('page_title', 'organizationStatus', 'organizationTypes'));
    }

    public function getData(Request $request)
    {
        return $this->organizationService->getAllOrganizations($request->all());
    }

    public function create()
    {
        $page_title = __('Add Organization');
        $organization = null;
        $organizationStatus = SwmOrganizationStatus::asSelectArray();
        $organizationTypes = $this->organizationTypeOptions();
        $wards = $this->wardOptions();

        return view('swm.service-providers.organizations.create', compact('page_title', 'organization', 'organizationStatus', 'organizationTypes', 'wards'));
    }

    public function store(OrganizationRequest $request)
    {
        $data = $request->all();
        $organizationId = $this->organizationService->storeOrUpdate(null, $data);

        if ($request->boolean('create_user')) {
            $data['swm_organization_id'] = $organizationId;
            $data['user_type'] = 'SW Organization';
            $data['roles'] = 'SW Organization - Admin';
            $data['gender'] = 'Male';
            $data['username'] = explode('@', (string) $request->email)[0];
            $data['status'] = UserStatus::Active;
            $this->userService->storeOrUpdate(null, $data);
            $successMessage = __('Organization and organization admin user created successfully.');
        } else {
            $successMessage = __('Organization created successfully.');
        }

        return redirect()->route('swm.organizations.index')->with('success', $successMessage);
    }

    public function show($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $page_title = __('Organization Details');
            $status = SwmOrganizationStatus::getDescription($organization->status);

            return view('swm.service-providers.organizations.show', compact('page_title', 'organization', 'status'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $organization = Organization::find($id);
        $organizationStatus = SwmOrganizationStatus::asSelectArray();
        $organizationTypes = $this->organizationTypeOptions();
        $wards = $this->wardOptions();
        if ($organization) {
            $page_title = __('Edit Organization');

            return view('swm.service-providers.organizations.edit', compact('page_title', 'organization', 'organizationStatus', 'organizationTypes', 'wards'));
        }

        abort(404);
    }

    public function update(OrganizationRequest $request, $id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $this->organizationService->storeOrUpdate((int) $organization->id, $request->all());

            return redirect()->route('swm.organizations.index')->with('success', __('Organization updated successfully.'));
        }

        return redirect()->route('swm.organizations.index')->with('error', __('Failed to update organization.'));
    }

    public function destroy($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            if ($organization->users()->exists()) {
                return redirect()->route('swm.organizations.index')->with('error', __('Cannot delete organization that has associated user information.'));
            }
            if ($organization->workers()->exists()) {
                return redirect()->route('swm.organizations.index')->with('error', __('Cannot delete organization that has associated worker information.'));
            }
            $organization->delete();

            return redirect()->route('swm.organizations.index')->with('success', __('Organization deleted successfully.'));
        }

        return redirect()->route('swm.organizations.index')->with('error', __('Failed to delete organization.'));
    }

    public function history($id)
    {
        $organization = Organization::find($id);
        if ($organization) {
            $page_title = __('Organization History');

            return view('swm.service-providers.organizations.history', compact('page_title', 'organization'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->organizationService->download($request->all());
    }
}
