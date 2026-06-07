<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\AttendanceLogRequest;
use App\Imports\Swm\AttendanceLogImport;
use App\Models\Swm\AttendanceLog;
use App\Models\Swm\Organization;
use App\Models\Swm\Worker;
use App\Services\Swm\AttendanceLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceLogController extends Controller
{
    use HandlesSwmExcelImport;

    public function __construct(
        protected AttendanceLogService $attendanceLogService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Attendance Logs', ['only' => ['index', 'getData']]);
        $this->middleware('permission:View SW Attendance Log', ['only' => ['show']]);
        $this->middleware('permission:Add SW Attendance Log', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Attendance Log', ['only' => ['edit', 'update']]);
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if (! $user || (! $user->can('Add SW Attendance Log') && ! $user->can('Edit SW Attendance Log'))) {
                abort(403);
            }

            return $next($request);
        })->only(['suggestionsWorkers', 'workerContext']);
        $this->middleware('permission:Delete SW Attendance Log', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Attendance Logs to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Attendance Logs From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Attendance Log History', ['only' => ['history']]);
    }

    protected function organizationOptionsForForms(): array
    {
        $query = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name');
        if (Auth::user()->swm_organization_id) {
            $query->where('id', Auth::user()->swm_organization_id);
        }

        return $query->pluck('name', 'id')->all();
    }

    protected function logBelongsToScopedOrg(?AttendanceLog $log): bool
    {
        if (! $log) {
            return false;
        }
        $oid = Auth::user()->swm_organization_id;

        return ! $oid || (int) $log->organization_id === (int) $oid;
    }

    public function index()
    {
        $page_title = __('Attendance');
        $organizations = Organization::query()->whereNull('deleted_at')->operational()->orderBy('name')->pluck('name', 'id');
        if (Auth::user()->swm_organization_id) {
            $organizations = Organization::query()->whereNull('deleted_at')->where('id', Auth::user()->swm_organization_id)->orderBy('name')->pluck('name', 'id');
        }
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = AttendanceLog::statusOptions();

        return view('swm.service-management.attendance-logs.index', compact(
            'page_title',
            'organizations',
            'scopedOrganizationId',
            'statusOptions'
        ));
    }

    public function getData(Request $request)
    {
        return $this->attendanceLogService->getAllAttendanceLogs($request->all());
    }

    public function create()
    {
        $page_title = __('Add Attendance Log');
        $attendanceLog = null;
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = AttendanceLog::statusOptions();

        return view('swm.service-management.attendance-logs.create', compact(
            'page_title',
            'attendanceLog',
            'organizations',
            'scopedOrganizationId',
            'statusOptions'
        ));
    }

    public function store(AttendanceLogRequest $request)
    {
        $id = $this->attendanceLogService->storeOrUpdate(null, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid worker or organization.'));
        }

        return redirect()->route('swm.attendance-logs.index')->with('success', __('Attendance log created successfully.'));
    }

    public function show(AttendanceLog $attendance_log)
    {
        if (! $this->logBelongsToScopedOrg($attendance_log)) {
            abort(403);
        }
        $page_title = __('Attendance Log Details');
        $attendanceLog = $attendance_log->load(['organization', 'worker']);

        return view('swm.service-management.attendance-logs.show', compact('page_title', 'attendanceLog'));
    }

    public function edit(AttendanceLog $attendance_log)
    {
        if (! $this->logBelongsToScopedOrg($attendance_log)) {
            abort(403);
        }
        $page_title = __('Edit Attendance Log');
        $attendanceLog = $attendance_log->load(['organization', 'worker']);
        $organizations = $this->organizationOptionsForForms();
        $scopedOrganizationId = Auth::user()->swm_organization_id;
        $statusOptions = AttendanceLog::statusOptions();

        return view('swm.service-management.attendance-logs.edit', compact(
            'page_title',
            'attendanceLog',
            'organizations',
            'scopedOrganizationId',
            'statusOptions'
        ));
    }

    public function update(AttendanceLogRequest $request, AttendanceLog $attendance_log)
    {
        if (! $this->logBelongsToScopedOrg($attendance_log)) {
            abort(403);
        }

        $id = $this->attendanceLogService->storeOrUpdate((int) $attendance_log->id, $request->validated());
        if ($id === null) {
            return redirect()->back()->withInput()->with('error', __('Invalid worker or organization.'));
        }

        return redirect()->route('swm.attendance-logs.index')->with('success', __('Attendance log updated successfully.'));
    }

    public function destroy(AttendanceLog $attendance_log)
    {
        if (! $this->logBelongsToScopedOrg($attendance_log)) {
            abort(403);
        }
        $attendance_log->delete();

        return redirect()->route('swm.attendance-logs.index')->with('success', __('Attendance log deleted successfully.'));
    }

    public function history(AttendanceLog $attendance_log)
    {
        if (! $this->logBelongsToScopedOrg($attendance_log)) {
            abort(403);
        }
        $page_title = __('Attendance Log History');
        $attendanceLog = $attendance_log;

        return view('swm.service-management.attendance-logs.history', compact('page_title', 'attendanceLog'));
    }

    public function export(Request $request)
    {
        return $this->attendanceLogService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->attendanceLogService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Attendance Logs from Excel'),
            route('swm.attendance-logs.index'),
            'swm.attendance-logs.import.store'
        );
    }

    public function importStore(Request $request)
    {
        $requiredHeaders = ['worker', 'entry_at', 'attendance_status'];
        if (! Auth::user()->swm_organization_id) {
            array_unshift($requiredHeaders, 'organization');
        }

        return $this->swmImportStore(
            $request,
            AttendanceLogImport::class,
            $requiredHeaders,
            'swm.attendance-logs.index',
            'importswm',
            'attendance-logs-import'
        );
    }

    public function suggestionsWorkers(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $orgId = (int) $validated['organization_id'];
        $this->authorizeOrganizationAccess($orgId);

        $term = trim((string) ($validated['q'] ?? ''));

        $query = Worker::query()
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId);

        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ILIKE', '%'.$term.'%')
                    ->orWhere('worker_id_no', 'ILIKE', '%'.$term.'%');
            });
        }

        $workers = $query->orderBy('name')->limit(50)->get(['id', 'name', 'worker_id_no']);

        $results = [];
        foreach ($workers as $w) {
            $suffix = $w->worker_id_no ? ' — '.$w->worker_id_no : '';
            $results[] = [
                'id' => (string) $w->id,
                'text' => $w->name.$suffix,
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function workerContext(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => ['required', 'integer'],
            'worker_id' => ['required', 'integer'],
        ]);

        $orgId = (int) $validated['organization_id'];
        $this->authorizeOrganizationAccess($orgId);

        $worker = Worker::query()
            ->whereNull('deleted_at')
            ->where('organization_id', $orgId)
            ->whereKey((int) $validated['worker_id'])
            ->with('workType')
            ->first();

        if (! $worker) {
            return response()->json(['error' => __('Worker not found.')], 404);
        }

        return response()->json([
            'work_type_name' => $worker->workType?->name ?? '',
            'supervisor_name' => $worker->supervisor_name ?? '',
            'department' => $worker->department ?? '',
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
