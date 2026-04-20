<?php

namespace App\Http\Controllers\Swm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Swm\BillCollectionPaymentRequest;
use App\Imports\BillCollectionPaymentImport;
use App\Models\Swm\BillCollectionPayment;
use App\Models\Swm\PrimaryCollectionSite;
use App\Models\User;
use App\Services\Swm\BillCollectionPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class BillCollectionPaymentController extends Controller
{
    public function __construct(protected BillCollectionPaymentService $billCollectionPaymentService)
    {
        $this->middleware('auth');
        $this->middleware('permission:List SWM Bill Collection Payments', ['only' => ['index', 'getData']]);
        $this->middleware('permission:List SWM Bill Collection Payments|Add SWM Bill Collection Payment|Edit SWM Bill Collection Payment|List SWM Billing Status', ['only' => [
            'holdingsSearch', 'customersByHolding', 'balanceThroughMonth',
        ]]);
        $this->middleware('permission:View SWM Bill Collection Payment', ['only' => ['show']]);
        $this->middleware('permission:Add SWM Bill Collection Payment', ['only' => ['create', 'store']]);
        $this->middleware('permission:Edit SWM Bill Collection Payment', ['only' => ['edit', 'update']]);
        $this->middleware('permission:Delete SWM Bill Collection Payment', ['only' => ['destroy']]);
        $this->middleware('permission:Export SWM Bill Collection Payments to CSV', ['only' => ['export']]);
        $this->middleware('permission:View SWM Bill Collection Payment History', ['only' => ['history']]);
        $this->middleware('permission:Import SWM Bill Collection Payments From CSV', ['only' => ['importForm', 'importStore']]);
    }

    public function index()
    {
        $page_title = __('Bill Collection Payments');

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

    public function customersByHolding(Request $request)
    {
        $request->validate([
            'holding_number' => ['required', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],
        ]);
        $rows = $this->billCollectionPaymentService->customersByHolding(
            $request->input('holding_number'),
            $request->filled('q') ? $request->input('q') : null
        );
        $results = [];
        foreach ($rows as $row) {
            $results[] = [
                'id' => (string) $row['id'],
                'text' => $row['text'],
                'customer_id' => $row['customer_id'],
                'holding_number' => $row['holding_number'],
                'waste_charge' => $row['waste_charge'],
                'using_this_service_since' => $row['using_this_service_since'],
                'survey_date' => $row['survey_date'],
            ];
        }

        return response()->json(['results' => $results]);
    }

    public function balanceThroughMonth(Request $request)
    {
        $validated = $request->validate([
            'primary_collection_site_id' => ['required', 'integer'],
            'payment_for_month' => ['required', 'date'],
            'exclude_payment_id' => ['nullable', 'integer'],
        ]);
        $site = PrimaryCollectionSite::query()
            ->whereKey($validated['primary_collection_site_id'])
            ->whereNull('deleted_at')
            ->first();
        if (! $site) {
            return response()->json(['error' => __('Primary collection site not found.')], 404);
        }
        $month = Carbon::parse($validated['payment_for_month'])->startOfMonth();
        $excludeId = isset($validated['exclude_payment_id']) ? (int) $validated['exclude_payment_id'] : null;

        return response()->json(
            $this->billCollectionPaymentService->balanceThroughMonth($site, $month, $excludeId ?: null)
        );
    }

    public function create()
    {
        $page_title = __('Add Bill Collection Payment');
        $payment = null;
        $paymentMethods = config('bill_collection.payment_methods', []);
        $users = User::query()->orderBy('name')->pluck('name', 'id');
        $canChooseReceivedBy = $this->userCanChooseBillCollectionReceivedBy();

        return view('swm.bill-collection.payments.create', compact(
            'page_title',
            'payment',
            'paymentMethods',
            'users',
            'canChooseReceivedBy'
        ));
    }

    public function store(BillCollectionPaymentRequest $request)
    {
        $data = $request->validated();
        $data = $this->finalizeBillCollectionReceivedByUserId($data, null);
        if (empty($data['received_by_user_id'])) {
            $data['received_by_user_id'] = Auth::id();
        }
        $id = $this->billCollectionPaymentService->storeOrUpdate(null, $data);
        if (! $id) {
            return redirect()->back()->withInput()->withErrors(['primary_collection_site_id' => __('Invalid primary collection site.')]);
        }
        $this->warnIfAmountExceedsDue($request, null);

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment created successfully.'));
    }

    public function show(BillCollectionPayment $payment)
    {
        $page_title = __('Bill Collection Payment Details');
        $payment->load(['primaryCollectionSite', 'receivedBy']);

        return view('swm.bill-collection.payments.show', compact('page_title', 'payment'));
    }

    public function edit(BillCollectionPayment $payment)
    {
        $page_title = __('Edit Bill Collection Payment');
        $paymentMethods = config('bill_collection.payment_methods', []);
        $users = User::query()->orderBy('name')->pluck('name', 'id');
        $payment->load(['primaryCollectionSite', 'receivedBy']);
        $canChooseReceivedBy = $this->userCanChooseBillCollectionReceivedBy();

        return view('swm.bill-collection.payments.edit', compact(
            'page_title',
            'payment',
            'paymentMethods',
            'users',
            'canChooseReceivedBy'
        ));
    }

    public function update(BillCollectionPaymentRequest $request, BillCollectionPayment $payment)
    {
        $data = $request->validated();
        $data = $this->finalizeBillCollectionReceivedByUserId($data, $payment);
        if (empty($data['received_by_user_id'])) {
            $data['received_by_user_id'] = Auth::id();
        }
        $id = $this->billCollectionPaymentService->storeOrUpdate($payment->id, $data);
        if (! $id) {
            return redirect()->back()->withInput()->withErrors(['primary_collection_site_id' => __('Invalid primary collection site.')]);
        }
        $this->warnIfAmountExceedsDue($request, $payment->id);

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment updated successfully.'));
    }

    public function destroy(BillCollectionPayment $payment)
    {
        $payment->delete();

        return redirect()->route('swm.bill-collection-payments.index')->with('success', __('Bill collection payment deleted successfully.'));
    }

    public function history(BillCollectionPayment $payment)
    {
        $page_title = __('Bill Collection Payment History');

        return view('swm.bill-collection.payments.history', compact('page_title', 'payment'));
    }

    public function importForm()
    {
        $page_title = __('Import Bill Collection Payments');

        return view('swm.bill-collection.payments.import', compact('page_title'));
    }

    public function importStore(Request $request)
    {
        Validator::extend('bill_collection_import_ext', function ($attribute, $value, $parameters, $validator) {
            $ext = strtolower((string) $value->getClientOriginalExtension());

            return in_array($ext, ['csv', 'xlsx'], true);
        }, __('File must be CSV or XLSX format.'));

        $this->validate($request, [
            'import_file' => 'required|file|bill_collection_import_ext',
        ], [
            'import_file.required' => __('The import file is required.'),
        ]);

        $extension = strtolower((string) $request->file('import_file')->getClientOriginalExtension());
        $filename = 'bill-collection-payments.'.$extension;
        if (Storage::disk('importbillcollectionpayments')->exists($filename)) {
            Storage::disk('importbillcollectionpayments')->delete($filename);
        }
        $stored = $request->file('import_file')->storeAs('/', $filename, 'importbillcollectionpayments');
        if (! $stored) {
            return redirect()->route('swm.bill-collection-payments.import')->with('error', __('Could not store the uploaded file.'));
        }
        $fullPath = Storage::disk('importbillcollectionpayments')->path($filename);

        $headings = (new HeadingRowImport)->toArray($fullPath);
        $headingRow = isset($headings[0][0]) ? array_map('strtolower', array_map('strval', $headings[0][0])) : [];
        $headingErrors = [];
        $required = ['customer_id', 'amount', 'payment_for_month', 'payment_method'];
        foreach ($required as $col) {
            if (! in_array($col, $headingRow, true)) {
                $headingErrors[$col] = __('Heading row is missing required column: :col', ['col' => $col]);
            }
        }
        if (count($headingErrors) > 0) {
            return back()->withErrors($headingErrors);
        }

        $import = new BillCollectionPaymentImport((int) Auth::id());
        Excel::import($import, $fullPath);

        $message = __('Imported :n bill collection payment(s).', ['n' => $import->successCount]);
        if (count($import->errors) > 0) {
            return redirect()->route('swm.bill-collection-payments.index')
                ->with('success', $message)
                ->with('import_errors', $import->errors);
        }

        return redirect()->route('swm.bill-collection-payments.index')->with('success', $message);
    }

    protected function warnIfAmountExceedsDue(BillCollectionPaymentRequest $request, ?int $excludeId): void
    {
        $site = PrimaryCollectionSite::query()
            ->whereKey($request->input('primary_collection_site_id'))
            ->whereNull('deleted_at')
            ->first();
        if (! $site) {
            return;
        }
        $month = Carbon::parse($request->input('payment_for_month'))->startOfMonth();
        $balance = $this->billCollectionPaymentService->balanceThroughMonth($site, $month, $excludeId);
        $due = $balance['due'] ?? null;
        if ($due === null) {
            return;
        }
        $amount = (string) $request->input('amount', '0');
        if (bccomp($amount, (string) $due, 2) > 0) {
            session()->flash('warning', __('The payment amount is greater than the calculated outstanding balance through the selected month. You may continue; verify the amount if needed.'));
        }
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
}
