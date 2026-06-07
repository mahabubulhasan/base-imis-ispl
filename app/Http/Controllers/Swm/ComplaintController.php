<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\ComplaintRequest;
use App\Imports\Swm\ComplaintImport;
use App\Models\Swm\Complaint;
use App\Services\Swm\BillCollectionPaymentService;
use App\Services\Swm\ComplaintService;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    use HandlesSwmExcelImport;
    protected ComplaintService $complaintService;

    public function __construct(
        ComplaintService $complaintService,
        protected BillCollectionPaymentService $billCollectionPaymentService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Complaints', ['only' => ['index', 'getData', 'holdingsSearch', 'customersSearch']]);
        $this->middleware('permission:View SW Complaint', ['only' => ['show']]);
        $this->middleware('permission:Add SW Complaint', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Complaint', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Complaint', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Complaints to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:Import SW Complaints From Excel', ['only' => ['importForm', 'importStore']]);
        $this->middleware('permission:View SW Complaint History', ['only' => ['history']]);
        $this->complaintService = $complaintService;
    }

    protected function selectOptions(): array
    {
        return [
            'complaintTypes' => config('swm_complaints.complaint_types', []),
            'submittedThroughOptions' => config('swm_complaints.submitted_through', []),
            'complaintStatuses' => config('swm_complaints.complaint_statuses', []),
            'priorityLevels' => [
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
            ],
            'duplicateOptions' => [
                0 => __('No'),
                1 => __('Yes'),
            ],
        ];
    }

    public function index()
    {
        $page_title = __('Complaints');
        $opts = $this->selectOptions();

        return view('swm.complaints.index', array_merge(compact('page_title'), $opts));
    }

    public function getData(Request $request)
    {
        return $this->complaintService->getAllComplaints($request->all());
    }

    public function create()
    {
        $page_title = __('Add Complaint');
        $complaint = null;
        $opts = $this->selectOptions();

        return view('swm.complaints.create', array_merge(compact('page_title', 'complaint'), $opts));
    }

    public function store(ComplaintRequest $request)
    {
        $this->complaintService->storeOrUpdate(null, $request->validated());

        return redirect()->route('swm.complaints.index')->with('success', __('SW complaint created successfully.'));
    }

    public function show($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('Complaint Details');

            return view('swm.complaints.show', compact('page_title', 'complaint'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('Edit Complaint');
            $opts = $this->selectOptions();

            return view('swm.complaints.edit', array_merge(compact('page_title', 'complaint'), $opts));
        }

        abort(404);
    }

    public function update(ComplaintRequest $request, $id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $this->complaintService->storeOrUpdate((int) $complaint->id, $request->validated());

            return redirect()->route('swm.complaints.index')->with('success', __('SW complaint updated successfully.'));
        }

        return redirect()->route('swm.complaints.index')->with('error', __('Failed to update SW complaint.'));
    }

    public function destroy($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $complaint->delete();

            return redirect()->route('swm.complaints.index')->with('success', __('SW complaint deleted successfully.'));
        }

        return redirect()->route('swm.complaints.index')->with('error', __('Failed to delete SW complaint.'));
    }

    public function history($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('Complaint History');

            return view('swm.complaints.history', compact('page_title', 'complaint'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->complaintService->download($request->all());
    }

    public function downloadTemplate()
    {
        $this->complaintService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Complaints'),
            route('swm.complaints.index'),
            'swm.complaints.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            $request,
            ComplaintImport::class,
            ['name', 'contact_number', 'complaint_type', 'complaint_details', 'submitted_through', 'complaint_status'],
            'swm.complaints.index',
            'importswm',
            'complaints'
        );
    }

    public function holdingsSearch(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }
        $map = $this->billCollectionPaymentService->searchHoldings($q, 30);
        $results = [];
        foreach ($map as $id => $text) {
            $results[] = ['id' => $id, 'text' => $text];
        }

        return response()->json(['results' => $results]);
    }

    public function customersSearch(Request $request)
    {
        $holdingNumbers = $request->input('holding_numbers', []);
        if (is_string($holdingNumbers)) {
            $holdingNumbers = trim($holdingNumbers) !== '' ? [$holdingNumbers] : [];
        } elseif (! is_array($holdingNumbers)) {
            $holdingNumbers = [];
        }
        $request->merge(['holding_numbers' => $holdingNumbers]);

        $request->validate([
            'holding_numbers' => ['nullable', 'array'],
            'holding_numbers.*' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $rows = $this->billCollectionPaymentService->customersForBillingFilter(
            $holdingNumbers,
            $request->input('q')
        );

        $results = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $results[] = [
                'id' => (string) ($row['household_id'] ?? ''),
                'text' => (string) ($row['text'] ?? ''),
                'household_id' => (string) ($row['household_id'] ?? ''),
                'holding_number' => (string) ($row['holding_number'] ?? ''),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
