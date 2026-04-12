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
        $this->middleware('permission:List SWM Vehicle Types', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SWM Vehicle Type', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Vehicle Type', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Vehicle Type', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Vehicle Type', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Vehicle Types to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Vehicle Type History', ['only' => ['history']]);
        $this->vehicleTypeService = $vehicleTypeService;
    }

    public function index()
    {
        $page_title = __('SWM Vehicle Types');

        return view('swm.service-providers.vehicle-types.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->vehicleTypeService->getAllVehicleTypes($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM Vehicle Type');
        $vehicleType = null;

        return view('swm.service-providers.vehicle-types.create', compact('page_title', 'vehicleType'));
    }

    public function store(VehicleTypeRequest $request)
    {
        $this->vehicleTypeService->storeOrUpdate(null, $request->all());

        return redirect()->route('swm.vehicle-types.index')->with('success', __('SWM vehicle type created successfully.'));
    }

    public function show($id)
    {
        $vehicleType = VehicleType::find($id);
        if ($vehicleType) {
            $page_title = __('SWM Vehicle Type Details');

            return view('swm.service-providers.vehicle-types.show', compact('page_title', 'vehicleType'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $vehicleType = VehicleType::find($id);
        if ($vehicleType) {
            $page_title = __('Edit SWM Vehicle Type');

            return view('swm.service-providers.vehicle-types.edit', compact('page_title', 'vehicleType'));
        }

        abort(404);
    }

    public function update(VehicleTypeRequest $request, $id)
    {
        $vehicleType = VehicleType::find($id);
        if ($vehicleType) {
            $this->vehicleTypeService->storeOrUpdate((int) $vehicleType->id, $request->all());

            return redirect()->route('swm.vehicle-types.index')->with('success', __('SWM vehicle type updated successfully.'));
        }

        return redirect()->route('swm.vehicle-types.index')->with('error', __('Failed to update SWM vehicle type.'));
    }

    public function destroy($id)
    {
        $vehicleType = VehicleType::find($id);
        if ($vehicleType) {
            if ($vehicleType->vehicles()->exists()) {
                return redirect()->route('swm.vehicle-types.index')->with('error', __('Cannot delete SWM vehicle type that has associated vehicles.'));
            }
            $vehicleType->delete();

            return redirect()->route('swm.vehicle-types.index')->with('success', __('SWM vehicle type deleted successfully.'));
        }

        return redirect()->route('swm.vehicle-types.index')->with('error', __('Failed to delete SWM vehicle type.'));
    }

    public function history($id)
    {
        $vehicleType = VehicleType::find($id);
        if ($vehicleType) {
            $page_title = __('SWM Vehicle Type History');

            return view('swm.service-providers.vehicle-types.history', compact('page_title', 'vehicleType'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->vehicleTypeService->download($request->all());
    }
}
