<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\LandfillLogRequest;
use App\Imports\Swm\LandfillLogImport;
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
    use HandlesSwmExcelImport;

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
        $this->middleware('permission:Export SW Landfill Logs to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Landfill Logs From Excel', ['only' => ['importForm', 'importStore']]);
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

    /**
     * STS id => list of source ward keys (for landfill log form STS wards display).
     *
     * @return array<string, list<string>>
     */
    protected function stsSourceWardsMap(): array
    {
        $map = [];
        Sts::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'source_wards', 'ward_no'])
            ->each(function (Sts $sts) use (&$map) {
                $map[(string) $sts->id] = $this->wardsForStsRecord($sts);
            });

        return $map;
    }

    /**
     * @return list<string>
     */
    protected function wardsForStsRecord(Sts $sts): array
    {
        $wardSet = [];
        foreach ($sts->source_wards ?? [] as $ward) {
            $normalized = $this->normalizeWardKey($ward);
            if ($normalized !== null) {
                $wardSet[$normalized] = true;
            }
        }
        if ($wardSet === [] && $sts->ward_no !== null) {
            $fallback = $this->normalizeWardKey($sts->ward_no);
            if ($fallback !== null) {
                $wardSet[$fallback] = true;
            }
        }

        $wards = array_keys($wardSet);
        sort($wards, SORT_NATURAL);

        return array_values($wards);
    }

    /**
     * @param  list<int>  $ids
     * @return list<string>
     */
    protected function unionWardsForStsIds(array $ids): array
    {
        $wardSet = [];
        Sts::query()
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->get(['id', 'source_wards', 'ward_no'])
            ->each(function (Sts $sts) use (&$wardSet) {
                foreach ($this->wardsForStsRecord($sts) as $ward) {
                    $wardSet[$ward] = true;
                }
            });

        $wards = array_keys($wardSet);
        sort($wards, SORT_NATURAL);

        return array_values($wards);
    }

    protected function normalizeWardKey(mixed $ward): ?string
    {
        $value = trim((string) $ward);
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return (string) (int) $value;
    }

    public function index()
    {
        $page_title = __('Landfill Loading');
        $landfillList = $this->landfillOptionsForForms();

        return view('swm.service-management.landfill-logs.index', compact(
            'page_title',
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
        $landfillList = $this->landfillOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();
        $stsSourceWardsMap = $this->stsSourceWardsMap();

        return view('swm.service-management.landfill-logs.create', compact(
            'page_title',
            'landfillLog',
            'landfillList',
            'stsList',
            'wardOptions',
            'stsSourceWardsMap'
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
        $page_title = __('Landfill Loading Log Details');
        $landfillLog = $landfill_log->load(['vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);
        $stsIds = is_array($landfillLog->source_sts_ids) ? array_values(array_filter(array_map('intval', $landfillLog->source_sts_ids), fn ($id) => $id > 0)) : [];
        $stsSourceWards = $this->unionWardsForStsIds($stsIds);
        $otherSourceWards = is_array($landfillLog->source_wards) ? $landfillLog->source_wards : [];

        return view('swm.service-management.landfill-logs.show', compact(
            'page_title',
            'landfillLog',
            'stsSourceWards',
            'otherSourceWards'
        ));
    }

    public function edit(LandfillLog $landfill_log)
    {
        $page_title = __('Edit Landfill Loading');
        $landfillLog = $landfill_log->load(['vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);
        $landfillList = $this->landfillOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();
        $stsSourceWardsMap = $this->stsSourceWardsMap();

        return view('swm.service-management.landfill-logs.edit', compact(
            'page_title',
            'landfillLog',
            'landfillList',
            'stsList',
            'wardOptions',
            'stsSourceWardsMap'
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

    public function downloadTemplate()
    {
        $this->landfillLogService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Landfill Logs from Excel'),
            route('swm.landfill-logs.index'),
            'swm.landfill-logs.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            $request,
            LandfillLogImport::class,
            ['vehicle_number', 'entry_at', 'operation_date'],
            'swm.landfill-logs.index',
            'importswm',
            'landfill-logs-import'
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
