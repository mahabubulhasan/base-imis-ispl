<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\StsLogRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Sts;
use App\Models\Swm\StsLog;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Services\Swm\StsLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StsLogController extends Controller
{
    public function __construct(
        protected StsLogService $stsLogService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW STS Logs', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW STS Log', ['only' => ['show']]);
        $this->middleware('permission:Add SW STS Log', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW STS Log', ['only' => ['edit', 'update']]);
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (! $user || (! $user->can('Add SW STS Log') && ! $user->can('Edit SW STS Log'))) {
                abort(403);
            }

            return $next($request);
        })->only(['suggestionsVehicles', 'vehicleContext', 'stsContext']);
        $this->middleware('permission:Delete SW STS Log', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW STS Logs to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW STS Log History', ['only' => ['history']]);
    }

    protected function stsOptionsForForms(): array
    {
        return Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function wasteTypeOptionsForForms(): array
    {
        return WasteType::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function wardOptionsForForms(): array
    {
        return Ward::getInAscOrder();
    }

    public function index()
    {
        $page_title = __('STS Daily Tracking');
        $statusOptions = StsLog::statusOptions();
        $stsList = $this->stsOptionsForForms();

        return view('swm.service-management.sts-logs.index', compact(
            'page_title',
            'statusOptions',
            'stsList'
        ));
    }

    public function getData(Request $request)
    {
        return $this->stsLogService->getAllStsLogs($request->all());
    }

    public function create()
    {
        $page_title = __('Add STS Log');
        $stsLog = null;
        $statusOptions = StsLog::statusOptions();
        $stsList = $this->stsOptionsForForms();
        $wasteTypeList = $this->wasteTypeOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.sts-logs.create', compact(
            'page_title',
            'stsLog',
            'statusOptions',
            'stsList',
            'wasteTypeList',
            'wardOptions'
        ));
    }

    public function store(StsLogRequest $request)
    {
        $id = $this->stsLogService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle.'));
        }

        return redirect()->route('swm.sts-logs.index')->with('success', __('STS log created successfully.'));
    }

    public function show(StsLog $sts_log)
    {
        $page_title = __('STS Log Details');
        $stsLog = $sts_log->load(['vehicle', 'vehicleType', 'driver', 'sts', 'wasteType']);

        return view('swm.service-management.sts-logs.show', compact('page_title', 'stsLog'));
    }

    public function edit(StsLog $sts_log)
    {
        $page_title = __('Edit STS Log');
        $stsLog = $sts_log->load(['vehicle', 'vehicleType', 'driver', 'sts', 'wasteType']);
        $statusOptions = StsLog::statusOptions();
        $stsList = $this->stsOptionsForForms();
        $wasteTypeList = $this->wasteTypeOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.sts-logs.edit', compact(
            'page_title',
            'stsLog',
            'statusOptions',
            'stsList',
            'wasteTypeList',
            'wardOptions'
        ));
    }

    public function update(StsLogRequest $request, StsLog $sts_log)
    {
        $id = $this->stsLogService->storeOrUpdate((int) $sts_log->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle.'));
        }

        return redirect()->route('swm.sts-logs.index')->with('success', __('STS log updated successfully.'));
    }

    public function destroy(StsLog $sts_log)
    {
        $sts_log->delete();

        return redirect()->route('swm.sts-logs.index')->with('success', __('STS log deleted successfully.'));
    }

    public function history(StsLog $sts_log)
    {
        $page_title = __('STS Log History');
        $stsLog = $sts_log;

        return view('swm.service-management.sts-logs.history', compact('page_title', 'stsLog'));
    }

    public function export(Request $request)
    {
        return $this->stsLogService->download($request->all());
    }

    public function suggestionsVehicles(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));

        $query = Vehicle::query()
            ->whereNull('deleted_at');

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('vehicle_number', 'ILIKE', '%'.$term.'%')
                    ->orWhere('vehicle_id_no', 'ILIKE', '%'.$term.'%');
            });
        }

        $vehicles = $query->orderBy('vehicle_number')->limit(50)->get(['id', 'vehicle_number', 'vehicle_id_no']);

        $results = [];
        foreach ($vehicles as $v) {
            $suffix = $v->vehicle_id_no ? ' — '.$v->vehicle_id_no : '';
            $results[] = [
                'id' => (string) $v->id,
                'text' => ($v->vehicle_number ?: '').$suffix,
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function vehicleContext(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => ['required', 'integer'],
        ]);

        $vehicle = Vehicle::query()
            ->whereNull('deleted_at')
            ->whereKey((int) $validated['vehicle_id'])
            ->with(['vehicleType', 'driver', 'dumpingSts'])
            ->first();

        if (! $vehicle) {
            return response()->json(['error' => __('Vehicle not found.')], 404);
        }

        $sts = $vehicle->dumpingSts;
        $wasteType = null;
        $wasteTypeId = null;
        $wards = [];
        if ($sts) {
            $wards = is_array($sts->source_wards) ? $sts->source_wards : [];
            $wasteTypes = $sts->wasteTypes();
            if ($wasteTypes->count() > 0) {
                $first = $wasteTypes->first();
                $wasteType = $first?->name;
                $wasteTypeId = $first?->id;
            }
        }

        return response()->json([
            'vehicle_type_id' => $vehicle->vehicle_type_id,
            'vehicle_type_name' => $vehicle->vehicleType?->name ?? '',
            'driver_name' => $vehicle->driver?->name ?? '',
            'capacity' => $vehicle->capacity,
            'sts_id' => $sts?->id,
            'sts_name' => $sts?->name ?? '',
            'waste_type_id' => $wasteTypeId,
            'waste_type_name' => $wasteType ?? '',
            'source_wards' => $wards,
        ]);
    }

    public function stsContext(Request $request)
    {
        $validated = $request->validate([
            'sts_id' => ['required', 'integer'],
        ]);

        $sts = Sts::query()
            ->whereNull('deleted_at')
            ->whereKey((int) $validated['sts_id'])
            ->first();

        if (! $sts) {
            return response()->json(['error' => __('STS not found.')], 404);
        }

        $wasteType = null;
        $wasteTypeId = null;
        $wasteTypes = $sts->wasteTypes();
        if ($wasteTypes->count() > 0) {
            $first = $wasteTypes->first();
            $wasteType = $first?->name;
            $wasteTypeId = $first?->id;
        }

        return response()->json([
            'sts_name' => $sts->name,
            'waste_type_id' => $wasteTypeId,
            'waste_type_name' => $wasteType ?? '',
            'source_wards' => is_array($sts->source_wards) ? $sts->source_wards : [],
        ]);
    }
}
