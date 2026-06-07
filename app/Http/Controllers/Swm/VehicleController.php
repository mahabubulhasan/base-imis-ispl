<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\VehicleRequest;
use App\Imports\Swm\VehicleImport;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\Organization;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\VehicleType;
use App\Models\Swm\Worker;
use App\Services\Swm\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    use HandlesSwmExcelImport;

    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Vehicles', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Vehicle', ['only' => ['show']]);
        $this->middleware('permission:Add SW Vehicle', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Vehicle', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Vehicle', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Vehicles to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Vehicles From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Vehicle History', ['only' => ['history']]);
        $this->vehicleService = $vehicleService;
    }

    protected function organizationOptionsForForms(): array
    {
        $query = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name');
        if (Auth::user()->swm_organization_id) {
            $query->where('id', Auth::user()->swm_organization_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function vehicleTypeOptionsForForms(): array
    {
        return VehicleType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function stsOptionsForForms(): array
    {
        return Sts::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function landfillOptionsForForms(): array
    {
        return Landfill::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all();
    }

    protected function wardOptionsForForms(): array
    {
        return Ward::getInAscOrder();
    }

    protected function vehicleBelongsToScopedOrg(?Vehicle $vehicle): bool
    {
        if (! $vehicle) {
            return false;
        }
        $oid = Auth::user()->swm_organization_id;

        return ! $oid || (int) $vehicle->organization_id === (int) $oid;
    }

    public function driversForOrganization(Request $request)
    {
        abort_unless(
            Auth::user()->can('Add SW Vehicle') || Auth::user()->can('Edit SW Vehicle'),
            403
        );

        $validated = $request->validate([
            'organization_id' => [
                'nullable',
                'integer',
                Rule::exists('pgsql.swm.organizations', 'id')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                }),
            ],
        ]);

        $scopedOrgId = Auth::user()->swm_organization_id ? (int) Auth::user()->swm_organization_id : null;
        $requestedOrgId = isset($validated['organization_id']) ? (int) $validated['organization_id'] : null;

        if ($scopedOrgId !== null) {
            if ($requestedOrgId !== null && $requestedOrgId !== $scopedOrgId) {
                abort(403);
            }

            return response()->json(
                $this->vehicleService->driverWorkersForOrganization($scopedOrgId)
            );
        }

        if ($requestedOrgId) {
            return response()->json(
                $this->vehicleService->driverWorkersForOrganization($requestedOrgId)
            );
        }

        return response()->json(
            $this->vehicleService->driverWorkersForOrganization(null)
        );
    }

    public function index()
    {
        $page_title = __('Vehicles');
        $organizations = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name')->pluck('name', 'id');
        if (Auth::user()->swm_organization_id) {
            $organizations = Organization::query()->whereNull('deleted_at')->where('id', Auth::user()->swm_organization_id)->orderBy('name')->pluck('name', 'id');
        }
        $vehicleTypes = VehicleType::query()->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id');
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        if ($scopedOrganizationId) {
            $driverWorkers = $this->vehicleService->driverWorkersForOrganization((int) $scopedOrganizationId);
        } else {
            $wtId = VehicleService::driverWorkTypeId();
            $driverWorkers = $wtId
                ? Worker::query()->where('work_type_id', $wtId)->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all()
                : [];
        }

        return view('swm.service-providers.vehicles.index', compact(
            'page_title',
            'organizations',
            'vehicleTypes',
            'driverWorkers',
            'scopedOrganizationId'
        ));
    }

    public function getData(Request $request)
    {
        return $this->vehicleService->getAllVehicles($request->all());
    }

    public function create()
    {
        $page_title = __('Add Vehicle');
        $vehicle = null;
        $organizations = $this->organizationOptionsForForms();
        $vehicleTypes = $this->vehicleTypeOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $landfills = $this->landfillOptionsForForms();
        $wards = $this->wardOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $driverWorkers = $this->vehicleService->driverWorkersForOrganization(
            $scopedOrganizationId ? (int) $scopedOrganizationId : null
        );

        $driversListUrl = route('swm.vehicles.drivers-for-organization');

        return view('swm.service-providers.vehicles.create', compact(
            'page_title',
            'vehicle',
            'organizations',
            'vehicleTypes',
            'stsList',
            'landfills',
            'wards',
            'scopedOrganizationId',
            'driverWorkers',
            'driversListUrl'
        ));
    }

    public function store(VehicleRequest $request)
    {
        $this->vehicleService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.vehicles.index')->with('success', __('Vehicle created successfully.'));
    }

    public function show(Vehicle $vehicle)
    {
        if ($this->vehicleBelongsToScopedOrg($vehicle)) {
            $vehicle->load(['organization', 'vehicleType', 'driver', 'dumpingSts', 'dumpingLandfill']);
            $page_title = __('Vehicle Details');
            $wards = $this->wardOptionsForForms();

            return view('swm.service-providers.vehicles.show', compact('page_title', 'vehicle', 'wards'));
        }

        abort(404);
    }

    public function edit(Vehicle $vehicle)
    {
        if ($this->vehicleBelongsToScopedOrg($vehicle)) {
            $page_title = __('Edit Vehicle');
            $organizations = $this->organizationOptionsForForms();
            $vehicleTypes = $this->vehicleTypeOptionsForForms();
            $stsList = $this->stsOptionsForForms();
            $landfills = $this->landfillOptionsForForms();
            $wards = $this->wardOptionsForForms();
            $scopedOrganizationId = Auth::user()->swm_organization_id;
            $driverWorkers = $this->vehicleService->driverWorkersForOrganization(
                $vehicle->organization_id !== null ? (int) $vehicle->organization_id : null
            );

            $driversListUrl = route('swm.vehicles.drivers-for-organization');

            return view('swm.service-providers.vehicles.edit', compact(
                'page_title',
                'vehicle',
                'organizations',
                'vehicleTypes',
                'stsList',
                'landfills',
                'wards',
                'scopedOrganizationId',
                'driverWorkers',
                'driversListUrl'
            ));
        }

        abort(404);
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        if ($this->vehicleBelongsToScopedOrg($vehicle)) {
            $this->vehicleService->storeOrUpdate((int) $vehicle->id, $request->all());

            return redirect()->route('swm.vehicles.index')->with('success', __('Vehicle updated successfully.'));
        }

        return redirect()->route('swm.vehicles.index')->with('error', __('Failed to update vehicle.'));
    }

    public function destroy(Vehicle $vehicle)
    {
        if ($this->vehicleBelongsToScopedOrg($vehicle)) {
            $vehicle->delete();

            return redirect()->route('swm.vehicles.index')->with('success', __('Vehicle deleted successfully.'));
        }

        return redirect()->route('swm.vehicles.index')->with('error', __('Failed to delete vehicle.'));
    }

    public function history(Vehicle $vehicle)
    {
        if ($this->vehicleBelongsToScopedOrg($vehicle)) {
            $page_title = __('Vehicle History');

            return view('swm.service-providers.vehicles.history', compact('page_title', 'vehicle'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->vehicleService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->vehicleService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Vehicles'),
            route('swm.vehicles.index'),
            'swm.vehicles.import.store'
        );
    }

    public function importStore(Request $request)
    {
        $required = ['vehicle_number', 'vehicle_type', 'driver'];
        if (! Auth::user()->swm_organization_id) {
            $required[] = 'organization';
        }

        return $this->swmImportStore(
            $request,
            VehicleImport::class,
            $required,
            'swm.vehicles.index',
            'importswm',
            'vehicles'
        );
    }
}
