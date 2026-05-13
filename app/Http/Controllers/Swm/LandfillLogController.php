<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\LandfillLogRequest;
use App\Models\LayerInfo\Ward;
use App\Models\Swm\Landfill;
use App\Models\Swm\LandfillLog;
use App\Models\Swm\Organization;
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
        })->only(['suggestionsVehicles', 'vehicleContext', 'landfillContext']);
        $this->middleware('permission:Delete SW Landfill Log', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Landfill Logs to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SW Landfill Log History', ['only' => ['history']]);
    }

    protected function organizationOptionsForForms(): array
    {
        $query = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name');
        if (Auth::user()->swm_organization_id) {
            $query->where('id', Auth::user()->swm_organization_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function landfillOptionsForForms(): array
    {
        return Landfill::query()
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

    protected function logBelongsToScopedOrg(?LandfillLog $log): bool
    {
        if (! $log) {
            return false;
        }
        $oid = Auth::user()->swm_organization_id;

        return ! $oid || (int) $log->organization_id === (int) $oid;
    }

    public function index()
    {
        $page_title = __('Landfill Daily Tracking');
        $organizations = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name')->pluck('name', 'id');
        if (Auth::user()->swm_organization_id) {
            $organizations = Organization::query()->whereNull('deleted_at')->where('id', Auth::user()->swm_organization_id)->orderBy('name')->pluck('name', 'id');
        }
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();

        return view('swm.service-management.landfill-logs.index', compact(
            'page_title',
            'organizations',
            'scopedOrganizationId',
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
        $page_title = __('Add Landfill Log');
        $landfillLog = null;
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();
        $wasteTypeList = $this->wasteTypeOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.landfill-logs.create', compact(
            'page_title',
            'landfillLog',
            'organizations',
            'scopedOrganizationId',
            'statusOptions',
            'landfillList',
            'wasteTypeList',
            'stsList',
            'wardOptions'
        ));
    }

    public function store(LandfillLogRequest $request)
    {
        $id = $this->landfillLogService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle or organization.'));
        }

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log created successfully.'));
    }

    public function show(LandfillLog $landfill_log)
    {
        if (! $this->logBelongsToScopedOrg($landfill_log)) {
            abort(403);
        }
        $page_title = __('Landfill Log Details');
        $landfillLog = $landfill_log->load(['organization', 'vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);

        return view('swm.service-management.landfill-logs.show', compact('page_title', 'landfillLog'));
    }

    public function edit(LandfillLog $landfill_log)
    {
        if (! $this->logBelongsToScopedOrg($landfill_log)) {
            abort(403);
        }
        $page_title = __('Edit Landfill Log');
        $landfillLog = $landfill_log->load(['organization', 'vehicle', 'vehicleType', 'driver', 'landfill', 'wasteType']);
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = LandfillLog::statusOptions();
        $landfillList = $this->landfillOptionsForForms();
        $wasteTypeList = $this->wasteTypeOptionsForForms();
        $stsList = $this->stsOptionsForForms();
        $wardOptions = $this->wardOptionsForForms();

        return view('swm.service-management.landfill-logs.edit', compact(
            'page_title',
            'landfillLog',
            'organizations',
            'scopedOrganizationId',
            'statusOptions',
            'landfillList',
            'wasteTypeList',
            'stsList',
            'wardOptions'
        ));
    }

    public function update(LandfillLogRequest $request, LandfillLog $landfill_log)
    {
        if (! $this->logBelongsToScopedOrg($landfill_log)) {
            abort(403);
        }

        $id = $this->landfillLogService->storeOrUpdate((int) $landfill_log->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid vehicle or organization.'));
        }

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log updated successfully.'));
    }

    public function destroy(LandfillLog $landfill_log)
    {
        if (! $this->logBelongsToScopedOrg($landfill_log)) {
            abort(403);
        }
        $landfill_log->delete();

        return redirect()->route('swm.landfill-logs.index')->with('success', __('Landfill log deleted successfully.'));
    }

    public function history(LandfillLog $landfill_log)
    {
        if (! $this->logBelongsToScopedOrg($landfill_log)) {
            abort(403);
        }
        $page_title = __('Landfill Log History');
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
            'organization_id' => ['required', 'integer'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $orgId = (int) $validated['organization_id'];
        $this->authorizeOrganizationAccess($orgId);

        $term = trim((string) ($validated['q'] ?? ''));

        $query = Vehicle::query()
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId);

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
            'organization_id' => ['required', 'integer'],
            'vehicle_id' => ['required', 'integer'],
        ]);

        $orgId = (int) $validated['organization_id'];
        $this->authorizeOrganizationAccess($orgId);

        $vehicle = Vehicle::query()
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId)
            ->whereKey((int) $validated['vehicle_id'])
            ->with(['vehicleType', 'driver', 'dumpingLandfill'])
            ->first();

        if (! $vehicle) {
            return response()->json(['error' => __('Vehicle not found.')], 404);
        }

        return response()->json([
            'vehicle_type_id' => $vehicle->vehicle_type_id,
            'vehicle_type_name' => $vehicle->vehicleType?->name ?? '',
            'driver_name' => $vehicle->driver?->name ?? '',
            'capacity' => $vehicle->capacity,
            'landfill_id' => $vehicle->dumpingLandfill?->id,
            'landfill_name' => $vehicle->dumpingLandfill?->name ?? '',
        ]);
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

        $wasteType = null;
        $wasteTypeId = null;
        $wasteTypes = $landfill->wasteTypes();
        if ($wasteTypes->count() > 0) {
            $first = $wasteTypes->first();
            $wasteType = $first?->name;
            $wasteTypeId = $first?->id;
        }

        $sourceSts = $landfill->sourceSts()->map(fn ($sts) => ['id' => $sts->id, 'name' => $sts->name])->values()->all();

        return response()->json([
            'landfill_name' => $landfill->name,
            'waste_type_id' => $wasteTypeId,
            'waste_type_name' => $wasteType ?? '',
            'source_sts_ids' => is_array($landfill->source_sts_ids) ? $landfill->source_sts_ids : [],
            'source_sts' => $sourceSts,
            'source_wards' => is_array($landfill->source_wards) ? $landfill->source_wards : [],
            'weighbridge_facility_available' => (bool) $landfill->weighbridge_facility_available,
        ]);
    }

    protected function authorizeOrganizationAccess(int $organizationId): void
    {
        $scoped = Auth::user()->swm_organization_id;
        if ($scoped && (int) $scoped !== $organizationId) {
            abort(403);
        }
    }
}
