<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\StsLogRequest;
use App\Imports\Swm\StsLogImport;
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
    use HandlesSwmExcelImport;

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
        })->only(['suggestionsVehicles', 'suggestionsWasteTypes', 'vehicleContext', 'stsContext']);
        $this->middleware('permission:Delete SW STS Log', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW STS Logs to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW STS Logs From Excel', ['only' => ['importForm', 'importStore']]);
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

    protected function wardOptionsForForms(): array
    {
        return Ward::getInAscOrder();
    }

    public function index()
    {
        $page_title = __('STS Loading');
        $stsList = $this->stsOptionsForForms();

        return view('swm.service-management.sts-logs.index', compact(
            'page_title',
            'stsList'
        ));
    }

    public function getData(Request $request)
    {
        return $this->stsLogService->getAllStsLogs($request->all());
    }

    public function create()
    {
        $page_title = __('Add STS Loading Log');
        $stsLog = null;
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.sts-logs.create', compact(
            'page_title',
            'stsLog',
            'stsList',
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
        $page_title = __('STS Loading Log Details');
        $stsLog = $sts_log->load(['vehicle', 'vehicleType', 'driver', 'sts', 'wasteType']);

        return view('swm.service-management.sts-logs.show', compact('page_title', 'stsLog'));
    }

    public function edit(StsLog $sts_log)
    {
        $page_title = __('Edit STS Loading Log');
        $stsLog = $sts_log->load(['vehicle', 'vehicleType', 'driver', 'sts', 'wasteType']);
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.sts-logs.edit', compact(
            'page_title',
            'stsLog',
            'stsList',
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
        $page_title = __('STS Loading Log History');
        $stsLog = $sts_log;

        return view('swm.service-management.sts-logs.history', compact('page_title', 'stsLog'));
    }

    public function export(Request $request)
    {
        return $this->stsLogService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->stsLogService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import STS Logs from Excel'),
            route('swm.sts-logs.index'),
            'swm.sts-logs.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            $request,
            StsLogImport::class,
            ['vehicle_number', 'entry_at', 'operation_date', 'sts_name'],
            'swm.sts-logs.index',
            'importswm',
            'sts-logs-import',
            __('STS Logs')
        );
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
            ->with(['vehicleType', 'driver', 'dumpingSts'])
            ->first();

        if (! $vehicle) {
            return response()->json(['error' => __('Vehicle not found.')], 404);
        }

        $sts = $vehicle->dumpingSts;
        $wards = [];
        if ($sts) {
            $wards = is_array($sts->source_wards) ? $sts->source_wards : [];
        }

        return response()->json([
            'vehicle_type_id' => $vehicle->vehicle_type_id,
            'vehicle_type_name' => $vehicle->vehicleType?->name ?? '',
            'driver_name' => $vehicle->driver?->name ?? '',
            'capacity' => $vehicle->capacity,
            'sts_id' => $sts?->id,
            'sts_name' => $sts?->name ?? '',
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

        $wasteTypes = $sts->wasteTypes();

        return response()->json([
            'sts_name' => $sts->name,
            'waste_type_ids' => $wasteTypes->pluck('id')->values()->all(),
            'waste_types' => $wasteTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values()->all(),
            'source_wards' => is_array($sts->source_wards) ? $sts->source_wards : [],
        ]);
    }
}
