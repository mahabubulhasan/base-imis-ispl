<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillLogRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\Sts;
use App\Models\Swm\Vehicle;
use App\Models\Swm\WasteType;
use App\Services\Swm\LandfillLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LandfillLogController extends Controller
{
    public function __construct(
        protected LandfillLogService $landfillLogService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Landfill Logs', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Landfill Log', ['only' => ['show']]);
        $this->middleware('permission:Add SW Landfill Log', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Landfill Log', ['only' => ['edit', 'update']]);
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (! $user || (! $user->can('Add SW Landfill Log') && ! $user->can('Edit SW Landfill Log'))) {
                abort(403);
            }

            return $next($request);
        })->only(['suggestionsVehicles', 'suggestionsWasteTypes', 'vehicleContext', 'landfillContext']);
        $this->middleware('permission:Delete SW Landfill Log', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Landfill Logs to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Landfill Log History', ['only' => ['history']]);
    }

    protected function landfillOptionsForForms(): array
    {
        return Landfill::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected function stsOptionsForForms(): array
    {
        return Sts::query()
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
        $page_title = __('Landfill Loading');
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();

        return view('swm.service-management.landfill-logs.index', compact(
            'page_title',
            'statusOptions',
            'landfillList'
        ));
    }

    public function getData(Request $request)
    {
        return $this->landfillLogService->getAllLandfillLogs($request->all());
    }

    public function create()
    {
        $page_title = __('Add Landfill Loading');
        $landfillLog = null;
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.landfill-logs.create', compact(
            'page_title',
            'landfillLog',
            'statusOptions',
            'landfillList',
            'stsList',
            'wardOptions'
        ));
    }

    public function store(LandfillLogRequest $request)
    {
        $id = $this->landfillLogService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle.'));
        }

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log created successfully.'));
    }

    public function show(LandfillLog $landfill_log)
    {
        $page_title = __('Landfill Loading Details');
        $landfillLog = $landfill_log->load(['vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);

        return view('swm.service-management.landfill-logs.show', compact('page_title', 'landfillLog'));
    }

    public function edit(LandfillLog $landfill_log)
    {
        $page_title = __('Edit Landfill Loading');
        $landfillLog = $landfill_log->load(['vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.landfill-logs.edit', compact(
            'page_title',
            'landfillLog',
            'statusOptions',
            'landfillList',
            'stsList',
            'wardOptions'
        ));
    }

    public function update(LandfillLogRequest $request, LandfillLog $landfill_log)
    {
        $id = $this->landfillLogService->storeOrUpdate((int) $landfill_log->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle.'));
        }

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log updated successfully.'));
    }

    public function destroy(LandfillLog $landfill_log)
    {
        $landfill_log->delete();

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log deleted successfully.'));
    }

    public function history(LandfillLog $landfill_log)
    {
        $page_title = __('Landfill Loading History');
        $landfillLog = $landfill_log;

        return view('swm.service-management.landfill-logs.history', compact('page_title', 'landfillLog'));
    }

    public function export(Request $request)
    {
        return $this->landfillLogService->download($request->all());
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

    public function suggestionsWasteTypes(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));

        $query = WasteType::query()
            ->whereNull('deleted_at');

        if ($term !== '') {
            $query->where('name', 'ILIKE', '%'.$term.'%');
        }

        $types = $query->orderBy('name')->limit(50)->get(['id', 'name']);

        $results = [];
        foreach ($types as $w) {
            $results[] = [
                'id' => (string) $w->id,
                'text' => $w->name,
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
            ->first();

        if (! $vehicle) {
            return response()->json(['error' => __('Vehicle not found.')], 404);
        }

        return response()->json($vehicle->toLandfillLogVehicleContextPayload());
    }

    public function landfillContext(Request $request)
    {
        $validated = $request->validate([
            'landfill_id' => ['required', 'integer'],
        ]);

        $landfill = Landfill::query()
            ->whereNull('deleted_at')
            ->whereKey((int) $validated['landfill_id'])
            ->first();

        if (! $landfill) {
            return response()->json(['error' => __('Landfill not found.')], 404);
        }

        $wasteTypes = $landfill->wasteTypes();

        $sourceSts = $landfill->sourceSts()->map(fn ($sts) => ['id' => $sts->id, 'name' => $sts->name])->values()->all();

        return response()->json([
            'landfill_name' => $landfill->name,
            'waste_type_ids' => $wasteTypes->pluck('id')->values()->all(),
            'waste_types' => $wasteTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values()->all(),
            'source_sts_ids' => is_array($landfill->source_sts_ids) ? $landfill->source_sts_ids : [],
            'source_sts' => $sourceSts,
            'source_wards' => is_array($landfill->source_wards) ? $landfill->source_wards : [],
            'weighbridge_facility_available' => (bool) $landfill->weighbridge_facility_available,
        ]);
    }
}
