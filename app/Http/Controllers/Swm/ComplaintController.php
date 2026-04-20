<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\ComplaintRequest;
use App\Models\Swm\Complaint;
use App\Services\Swm\BillCollectionPaymentService;
use App\Services\Swm\ComplaintService;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    protected ComplaintService $complaintService;

    public function __construct(
        ComplaintService $complaintService,
        protected BillCollectionPaymentService $billCollectionPaymentService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Complaints', ['only' => ['index', 'getData', 'holdingsSearch', 'customersSearch']]);
        $this->middleware('permission:View SWM Complaint', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Complaint', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Complaint', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Complaint', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Complaints to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Complaint History', ['only' => ['history']]);
        $this->complaintService = $complaintService;
    }

    protected function selectOptions(): array
    {
        return [
            'complaintTypes' => config('swm_complaints.complaint_types', []),
            'submittedThroughOptions' => config('swm_complaints.submitted_through', []),
            'complaintStatuses' => config('swm_complaints.complaint_statuses', []),
        ];
    }

    public function index()
    {
        $page_title = __('SWM Complaints');
        $opts = $this->selectOptions();

        return view('swm.complaints.index', array_merge(compact('page_title'), $opts));
    }

    public function getData(Request $request)
    {
        return $this->complaintService->getAllComplaints($request->all());
    }

    public function create()
    {
        $page_title = __('Add SWM Complaint');
        $complaint = null;
        $opts = $this->selectOptions();

        return view('swm.complaints.create', array_merge(compact('page_title', 'complaint'), $opts));
    }

    public function store(ComplaintRequest $request)
    {
        $this->complaintService->storeOrUpdate(null, $request->validated());

        return redirect()->route('swm.complaints.index')->with('success', __('SWM complaint created successfully.'));
    }

    public function show($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('SWM Complaint Details');

            return view('swm.complaints.show', compact('page_title', 'complaint'));
        }

        abort(404);
    }

    public function edit($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('Edit SWM Complaint');
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

            return redirect()->route('swm.complaints.index')->with('success', __('SWM complaint updated successfully.'));
        }

        return redirect()->route('swm.complaints.index')->with('error', __('Failed to update SWM complaint.'));
    }

    public function destroy($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $complaint->delete();

            return redirect()->route('swm.complaints.index')->with('success', __('SWM complaint deleted successfully.'));
        }

        return redirect()->route('swm.complaints.index')->with('error', __('Failed to delete SWM complaint.'));
    }

    public function history($id)
    {
        $complaint = Complaint::find($id);
        if ($complaint) {
            $page_title = __('SWM Complaint History');

            return view('swm.complaints.history', compact('page_title', 'complaint'));
        }

        abort(404);
    }

    public function export(Request $request)
    {
        return $this->complaintService->download($request->all());
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
                'id' => (string) ($row['customer_id'] ?? ''),
                'text' => (string) ($row['text'] ?? ''),
                'customer_id' => (string) ($row['customer_id'] ?? ''),
                'holding_number' => (string) ($row['holding_number'] ?? ''),
            ];
        }

        return response()->json(['results' => $results]);
    }
}
