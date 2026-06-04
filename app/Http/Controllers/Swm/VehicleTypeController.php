<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\VehicleTypeRequest;
use App\Models\Swm\VehicleType;
use App\Services\Swm\VehicleTypeService;
use Illuminate\Http\Request;

class VehicleTypeController extends Controller
{
    protected VehicleTypeService $vehicleTypeService;

    public function __construct(VehicleTypeService $vehicleTypeService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Vehicle Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Vehicle Type', ['only' => ['show']]);
        $this->middleware('permission:Add SW Vehicle Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Vehicle Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Vehicle Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Vehicle Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Vehicle Type History', ['only' => ['history']]);
        $this->vehicleTypeService = $vehicleTypeService;
    }

    public function index()
    {
        $page_title = __('SW Vehicle Types');

        return view('swm.service-providers.vehicle-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->vehicleTypeService->getAllVehicleTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SW Vehicle Type');
        $vehicleType = null;

        return view('swm.service-providers.vehicle-types.create', compact('page_title', 'vehicleType'));
    }

    public function store(VehicleTypeRequest $request)
    {
        $this->vehicleTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.vehicle-types.index')->with('success', __('SW vehicle type created successfully.'));
    }

    public function show(VehicleType $vehicle_type)
    {
        $page_title = __('SW Vehicle Type Details');

        return view('swm.service-providers.vehicle-types.show', [
            'page_title' => $page_title,
            'vehicleType' => $vehicle_type,
        ]);
    }

    public function edit(VehicleType $vehicle_type)
    {
        $page_title = __('Edit SW Vehicle Type');

        return view('swm.service-providers.vehicle-types.edit', [
            'page_title' => $page_title,
            'vehicleType' => $vehicle_type,
        ]);
    }

    public function update(VehicleTypeRequest $request, VehicleType $vehicle_type)
    {
        $this->vehicleTypeService->storeOrUpdate((int) $vehicle_type->id, $request->all());

        return redirect()->route('swm.vehicle-types.index')->with('success', __('SW vehicle type updated successfully.'));
    }

    public function destroy(VehicleType $vehicle_type)
    {
        if ($vehicle_type->vehicles()->exists()) {
            return redirect()->route('swm.vehicle-types.index')->with('error', __('Cannot delete SW vehicle type that has associated vehicles.'));
        }

        $vehicle_type->delete();

        return redirect()->route('swm.vehicle-types.index')->with('success', __('SW vehicle type deleted successfully.'));
    }

    public function history(VehicleType $vehicle_type)
    {
        $page_title = __('SW Vehicle Type History');

        return view('swm.service-providers.vehicle-types.history', [
            'page_title' => $page_title,
            'vehicleType' => $vehicle_type,
        ]);
    }

    public function export(Request $request)
    {
        return $this->vehicleTypeService->download($request->all());
    }
}
