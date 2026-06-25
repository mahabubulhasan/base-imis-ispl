<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Swm\Concerns\HandlesSwmExcelImport;
use App\Http\Requests\Swm\BillCollectionPaymentRequest;
use App\Imports\Swm\BillCollectionPaymentImport;
use App\Models\Swm\BillCollectionPayment;
use App\Models\BuildingInfo\Household;
use App\Models\LayerInfo\Ward;
use App\Models\User;
use App\Services\Swm\BillCollectionPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillCollectionPaymentController extends Controller
{
    use HandlesSwmExcelImport;
    public function __construct(protected BillCollectionPaymentService $billCollectionPaymentService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SW Bill Collection Payments', ['only' => ['index', 'getData']]);
        $this->middleware('permission:List SW Bill Collection Payments|Add SW Bill Collection Payment|Edit SW Bill Collection Payment|List SW Billing Status', ['only' => [
            'holdingsSearch', 'customersByHolding', 'balanceThroughMonth',
        ]]);
        $this->middleware('permission:View SW Bill Collection Payment', ['only' => ['show']]);
        $this->middleware('permission:Add SW Bill Collection Payment', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SW Bill Collection Payment', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SW Bill Collection Payment', ['only' => ['destroy']]);
        $this->middleware('permission:Export SW Bill Collection Payments to Excel', ['only' => ['export', 'downloadTemplate']]);
        $this->middleware('permission:View SW Bill Collection Payment History', ['only' => ['history']]);
        $this->middleware('permission:Import SW Bill Collection Payments From Excel', ['only' => ['importForm', 'importStore']]);
    }

    public function index()
    {
        $page_title = __('Payments');

        return view('swm.bill-collection.payments.index', compact('page_title'));
    }

    public function getData(Request $request)
    {
        return $this->billCollectionPaymentService->getAllPayments($request->all());
    }

    public function export(Request $request)
    {
        $this->billCollectionPaymentService->download($request->all());
    }

    public function holdingsSearch(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $ward = $request->filled('ward') ? (int) $request->get('ward') : null;
        $page = max(1, (int) $request->get('page', 1));
        // With a ward selected the dropdown auto-loads that ward's holdings (no search term needed);
        // without a ward, keep the 2-character minimum to avoid loading every holding.
        if ($ward === null && strlen($q) < 2) {
            return response()->json(['results' => [], 'pagination' => ['more' => false]]);
        }
        $result = $this->billCollectionPaymentService->searchHoldings($q, 20, $ward, $page);

        return response()->json([
            'results' => $result['results'],
            'pagination' => ['more' => $result['has_more']],
        ]);
    }

    public function customersByHolding(Request $request)
    {
        $request->validate([
            'holding_number' => ['required', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'integer'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $result = $this->billCollectionPaymentService->customersByHolding(
            $request->input('holding_number'),
            $request->filled('q') ? $request->input('q') : null,
            $request->filled('ward') ? (int) $request->input('ward') : null,
            max(1, (int) $request->get('page', 1))
        );
        $results = [];
        foreach ($result['results'] as $row) {
            $results[] = [
                'id' => (string) $row['id'],
                'text' => $row['text'],
                'household_id' => $row['household_id'],
                'household_owner_name' => $row['household_owner_name'],
                'father_or_husband_name' => $row['father_or_husband_name'],
                'holding_number' => $row['holding_number'],
                'contact_number' => $row['contact_number'],
                'sub_location' => $row['sub_location'],
                'ward' => $row['ward'],
                'road_no' => $row['road_no'],
                'road_name' => $row['road_name'],
                'waste_charge' => $row['waste_charge'],
                'using_this_service_since' => $row['using_this_service_since'],
                'survey_date' => $row['survey_date'],
            ];
        }

        return response()->json([
            'results' => $results,
            'pagination' => ['more' => $result['has_more']],
        ]);
    }

    public function balanceThroughMonth(Request $request)
    {
        $validated = $request->validate([
            'household_id' => ['required', 'integer'],
            'payment_for_month' => ['required', 'date'],
            'exclude_payment_id' => ['nullable', 'integer'],
        ]);
        $site = Household::query()
            ->whereKey($validated['household_id'])
            ->whereNull('deleted_at')
            ->first();
        if (! $site) {
            return response()->json(['error' => __('Household not found.')], 404);
        }
        $month = Carbon::parse($validated['payment_for_month'])->startOfMonth();
        $excludeId = isset($validated['exclude_payment_id']) ? (int) $validated['exclude_payment_id'] : null;

        return response()->json(
            $this->billCollectionPaymentService->balanceThroughMonth($site, $month, $excludeId ?: null)
        );
    }

    public function create()
    {
        $page_title = __('Add Payment');
        $payment = null;
        $paymentMethods = config('bill_collection.payment_methods', []);
        $users = User::query()->orderBy('name')->pluck('name', 'id');
        $wards = Ward::getInAscOrder();
        $canChooseReceivedBy = $this->userCanChooseBillCollectionReceivedBy();

        return view('swm.bill-collection.payments.create', compact(
            'page_title',
            'payment',
            'paymentMethods',
            'users',
            'wards',
            'canChooseReceivedBy'
        ));
    }

    public function store(BillCollectionPaymentRequest $request)
    {
        $data = $request->validated();
        $exceedError = $this->validateCollectedAmountWithinDue($request, null);
        if ($exceedError !== null) {
            return redirect()->back()->withInput()->withErrors(['amount' => $exceedError]);
        }
        $data = $this->finalizeBillCollectionReceivedByUserId($data, null);
        if (empty($data['received_by_user_id'])) {
            $data['received_by_user_id'] = Auth::id();
        }
        $data['receipt_copy_path'] = $this->storeReceiptCopyIfPresent($request);
        $id = $this->billCollectionPaymentService->storeOrUpdate(null, $data);
        if (! $id) {
            return redirect()->back()->withInput()->withErrors(['household_id' => __('Invalid household.')]);
        }

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment created successfully.'));
    }

    public function show(BillCollectionPayment $payment)
    {
        $page_title = __('Payment Details');
        $payment->load(['primaryCollectionSite', 'receivedBy']);

        return view('swm.bill-collection.payments.show', compact('page_title', 'payment'));
    }

    public function edit(BillCollectionPayment $payment)
    {
        $page_title = __('Edit Payment');
        $paymentMethods = config('bill_collection.payment_methods', []);
        $users = User::query()->orderBy('name')->pluck('name', 'id');
        $wards = Ward::getInAscOrder();
        $payment->load(['primaryCollectionSite', 'receivedBy']);
        $canChooseReceivedBy = $this->userCanChooseBillCollectionReceivedBy();

        return view('swm.bill-collection.payments.edit', compact(
            'page_title',
            'payment',
            'paymentMethods',
            'users',
            'wards',
            'canChooseReceivedBy'
        ));
    }

    public function update(BillCollectionPaymentRequest $request, BillCollectionPayment $payment)
    {
        $data = $request->validated();
        $exceedError = $this->validateCollectedAmountWithinDue($request, $payment->id);
        if ($exceedError !== null) {
            return redirect()->back()->withInput()->withErrors(['amount' => $exceedError]);
        }
        $data = $this->finalizeBillCollectionReceivedByUserId($data, $payment);
        if (empty($data['received_by_user_id'])) {
            $data['received_by_user_id'] = Auth::id();
        }
        $data['receipt_copy_path'] = $payment->receipt_copy_path;
        if ($request->hasFile('receipt_copy')) {
            $newReceiptPath = $this->storeReceiptCopyIfPresent($request);
            if ($newReceiptPath) {
                $this->deleteReceiptCopyIfPresent($payment->receipt_copy_path);
                $data['receipt_copy_path'] = $newReceiptPath;
            }
        }
        $id = $this->billCollectionPaymentService->storeOrUpdate($payment->id, $data);
        if (! $id) {
            return redirect()->back()->withInput()->withErrors(['household_id' => __('Invalid household.')]);
        }

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment updated successfully.'));
    }

    public function destroy(BillCollectionPayment $payment)
    {
        $this->deleteReceiptCopyIfPresent($payment->receipt_copy_path);
        $payment->delete();

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment deleted successfully.'));
    }

    public function history(BillCollectionPayment $payment)
    {
        $page_title = __('Payment History');

        return view('swm.bill-collection.payments.history', compact('page_title', 'payment'));
    }

    public function downloadTemplate()
    {
        $this->billCollectionPaymentService->downloadTemplate();
    }

    public function importForm()
    {
        return $this->swmImportFormView(
            __('Import Bill Collection Payments'),
            route('swm.bill-collection-payments.index'),
            'swm.bill-collection-payments.import.store'
        );
    }

    public function importStore(Request $request)
    {
        return $this->swmImportStore(
            request: $request,
            importClass: BillCollectionPaymentImport::class,
            requiredHeaders: $this->billCollectionPaymentService->requiredImportLabels(),
            indexRoute: 'swm.bill-collection-payments.index',
            disk: 'importbillcollectionpayments',
            filenamePrefix: 'bill-collection-payments',
            entityName: __('Bill Collection'),
        );
    }

    protected function validateCollectedAmountWithinDue(BillCollectionPaymentRequest $request, ?int $excludeId): ?string
    {
        $site = Household::query()
            ->whereKey($request->input('household_id'))
            ->whereNull('deleted_at')
            ->first();
        if (! $site) {
            return null;
        }
        $month = Carbon::parse($request->input('payment_for_month'))->startOfMonth();
        $balance = $this->billCollectionPaymentService->balanceThroughMonth($site, $month, $excludeId);
        $due = $balance['due'] ?? null;
        if ($due === null) {
            return null;
        }
        $amount = (string) $request->input('amount', '0');
        $duePaid = (string) $request->input('due_paid', '0');
        $totalCollected = bcadd($amount, $duePaid, 2);
        if (bccomp($totalCollected, (string) $due, 2) > 0) {
            return __('Total collected amount (current month + previous due) cannot be greater than the calculated outstanding balance through the selected month.');
        }

        return null;
    }

    protected function userCanChooseBillCollectionReceivedBy(): bool
    {
        $user = Auth::user();

        return $user->hasRole('Super Admin') || $user->hasRole('Municipality - Super Admin');
    }

    /**
     * Non-admins cannot change "Payment received by"; ignore posted value.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function finalizeBillCollectionReceivedByUserId(array $data, ?BillCollectionPayment $existing): array
    {
        if ($this->userCanChooseBillCollectionReceivedBy()) {
            return $data;
        }
        if ($existing) {
            $data['received_by_user_id'] = $existing->received_by_user_id;
        } else {
            $data['received_by_user_id'] = Auth::id();
        }

        return $data;
    }

    protected function storeReceiptCopyIfPresent(Request $request): ?string
    {
        if (! $request->hasFile('receipt_copy')) {
            return null;
        }

        return $request->file('receipt_copy')->store('bill-collection-receipts', 'public');
    }

    protected function deleteReceiptCopyIfPresent(?string $path): void
    {
        if (! $path) {
            return;
        }
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
