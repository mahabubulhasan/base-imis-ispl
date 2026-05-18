<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Models\Swm\Worker;
use App\Services\Swm\BillCollectionBillingStatusService;
use App\Services\Swm\BillCollectionPaymentService;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BillCollectionBillingStatusController extends Controller
{
    public function __construct(
        protected BillCollectionBillingStatusService $billingStatusService,
        protected BillCollectionPaymentService $billCollectionPaymentService
    ) {
        $this->middleware('auth');
        $this->middleware('permission:List SW Billing Status', ['only' => ['index', 'getData', 'summary', 'customersSearch', 'downloadPdf']]);
    }

    public function index()
    {
        $page_title = __('Billing Status');
        $vanPullers = Worker::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();

        return view('swm.bill-collection.billing-status.index', compact('page_title', 'vanPullers'));
    }

    public function getData(Request $request)
    {
        $request->merge([
            'van_puller_id' => $request->filled('van_puller_id') ? $request->input('van_puller_id') : null,
            'is_owner' => $request->input('is_owner') === '' ? null : $request->input('is_owner'),
        ]);

        $request->validate([
            'month_from' => ['nullable', 'date_format:Y-m'],
            'month_to' => ['nullable', 'date_format:Y-m'],
            'is_owner' => ['nullable', 'in:0,1'],
            'van_puller_id' => ['nullable', 'integer', 'min:1'],
            'holding_number' => ['nullable', 'string', 'max:255'],
            'household_id' => ['nullable', 'string', 'max:255'],
            'holding_numbers' => ['nullable', 'array'],
            'holding_numbers.*' => ['nullable', 'string', 'max:255'],
            'customer_site_ids' => ['nullable', 'array'],
            'customer_site_ids.*' => ['nullable', 'integer', 'min:1'],
        ]);

        return $this->billingStatusService->getStatusTable($request->all() ?? []);
    }

    public function customersSearch(Request $request): JsonResponse
    {
        $request->validate([
            'holding_numbers' => ['nullable', 'array'],
            'holding_numbers.*' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);

        $holdingNumbers = $request->input('holding_numbers', []);
        if (! is_array($holdingNumbers)) {
            $holdingNumbers = $holdingNumbers !== null && $holdingNumbers !== '' ? [(string) $holdingNumbers] : [];
        }

        $rows = $this->billCollectionPaymentService->customersForBillingFilter(
            $holdingNumbers,
            $request->input('q')
        );
        if (! is_array($rows)) {
            $rows = [];
        }

        $results = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $results[] = [
                'id' => (string) ($row['id'] ?? ''),
                'text' => (string) ($row['text'] ?? ''),
                'household_id' => (string) ($row['household_id'] ?? ''),
                'holding_number' => (string) ($row['holding_number'] ?? ''),
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function summary(): JsonResponse
    {
        return response()->json($this->billingStatusService->summary());
    }

    public function downloadPdf(Request $request)
    {
        $request->merge([
            'van_puller_id' => $request->filled('van_puller_id') ? $request->input('van_puller_id') : null,
            'is_owner' => $request->input('is_owner') === '' ? null : $request->input('is_owner'),
        ]);

        $request->validate([
            'month_from' => ['nullable', 'date_format:Y-m'],
            'month_to' => ['nullable', 'date_format:Y-m'],
            'is_owner' => ['nullable', 'in:0,1'],
            'van_puller_id' => ['nullable', 'integer', 'min:1'],
            'holding_numbers' => ['nullable', 'array'],
            'holding_numbers.*' => ['nullable', 'string', 'max:255'],
            'customer_site_ids' => ['nullable', 'array'],
            'customer_site_ids.*' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $this->billingStatusService->getStatusRowsForExport($request->all() ?? []);

        $pdf = PDF::loadView('swm.bill-collection.billing-status.pdf', [
            'rows' => $result['rows'] ?? [],
            'monthFrom' => $result['month_from'] ?? null,
            'monthTo' => $result['month_to'] ?? null,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('encoding', 'UTF-8')
            ->setOption('enable-local-file-access', true)
            ->setOption('footer-line', false)
            ->setOption('footer-html', null)
            ->setOption('enable-javascript', false)
            ->setOption('javascript-delay', 0);

        $monthFrom = $result['month_from'] ?? null;
        $monthTo = $result['month_to'] ?? null;
        $fromLabel = $monthFrom instanceof \Carbon\Carbon ? $monthFrom->format('Y-m') : 'na';
        $toLabel = $monthTo instanceof \Carbon\Carbon ? $monthTo->format('Y-m') : 'na';
        $filename = "billing-status-{$fromLabel}_to_{$toLabel}.pdf";

        return $pdf->download($filename);
    }
}
