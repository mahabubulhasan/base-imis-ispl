<?php
// Last Modified: 2026-07-05
// Developed By: Streams Tech Ltd.
// Description: Handles payment checkout, receipt generation, and payment status pages

namespace App\Http\Controllers;

use App\Models\Fsm\Application;
use App\Models\Payment;
use Illuminate\Http\Request;
use Streamstech\Ekpay\Customer;
use Streamstech\Ekpay\EkpayService;
use Streamstech\Ekpay\Transaction;
use PDF;

class PaymentController extends Controller
{
    public function checkout($application_id)
    {
        // Fetch the application record based on application_id
        $application = Application::find($application_id);

        if (!$application) {
            return redirect()->back()->with('error', 'Application record not found for the given application ID.');
        }

        return view('payment.checkout', compact('application'));
    }

    public function store(EkpayService $ekpayService, $application_id)
    {
        // Fetch the application record
        $application = Application::find($application_id);

        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }

        $contactNumber = $application->applicant_contact;

        // Add prefix 0 if contact number is 10 digits
        if (strlen($contactNumber) === 10) {
            $contactNumber = '0' . $contactNumber;
        }

        $payment = Payment::createPayment(
            $application->applicant_name,
            $contactNumber,
            $application->customer_name ?? $application->applicant_name,
            $application->address,
            $application->tax_code,
            $application_id,
            $application->proposed_emptying_date,
            1500
        );

        $customer = new Customer(
            $payment->applicant_name,
            $payment->applicant_contact,
            'CUST-' . substr(sha1($payment->applicant_contact), 0, 6)
        );

        $transaction = new Transaction(
            $payment->transaction_id,
            '1500.00'
        );

        session()->put('payment_id', $payment->id);

        return $ekpayService->send($customer, $transaction);
    }

    public function success()
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
    }

    public function failed()
    {
        return view('payment.failed');
    }

    public function receipt($transaction_id)
    {
        $payment = Payment::where('transaction_id', $transaction_id)->firstOrFail();
        return view('payment.receipt', compact('payment'));
    }

    public function downloadReceipt($transaction_id)
    {
        // Last Modified: 2026-07-05
        // Developed By: Streams Tech Ltd.
        // Description: Download receipt as PDF

        $payment = Payment::where('transaction_id', $transaction_id)->firstOrFail();

        // Generate PDF from receipt view with styling
        $pdf = \PDF::loadView('payment.receipt', compact('payment'));

        // Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Generate filename: yes-{receipt_no}.pdf
        $filename = 'yes-' . $payment->receipt_no . '.pdf';

        // Download PDF
        return $pdf->download($filename);
    }

    public function ipn(Request $request)
    {
        $trnxId = $request->trnx_info['mer_trnx_id'];
        switch ($request->msg_code) {
            case 1020:
                Payment::updateTransactionStatus($trnxId, "Paid");
                break;
            case 1021:
                Payment::updateTransactionStatus($trnxId, "Failed");
                break;
            case 1022:
                Payment::updateTransactionStatus($trnxId, "Canceled");
                break;
        }

        return response()->json([
            'ack_code' => 'ack'.time(),
            'ack_msg' => 'Acknowledge Successfully.',
            'ack_timestamp' => now()->toDateTimeString()
        ]);
    }

    public function history()
    {
        // Last Modified: 2026-07-06
        // Developed By: Streams Tech Ltd.
        // Description: Display payment history with filters

        return view('payment.history');
    }

    public function getPaymentData(Request $request)
    {
        // Last Modified: 2026-07-06
        // Developed By: Streams Tech Ltd.
        // Description: Fetch payment data for DataTable with server-side processing

        $columns = ['id', 'transaction_id', 'applicant_name', 'amount', 'status', 'created_at', 'applicant_contact', 'receipt_no', 'customer_name'];
        $columnIndex = $request->input('order.0.column', 0);
        $columnSortOrder = $request->input('order.0.dir', 'desc');
        $columnName = $columns[$columnIndex] ?? 'created_at';

        $searchValue = $request->input('search.value', '');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        // Build query
        $query = Payment::query();

        // Apply filters
        if ($request->filled('applicant_name')) {
            $query->where('applicant_name', 'like', '%' . $request->input('applicant_name') . '%');
        }

        if ($request->filled('transaction_id')) {
            $query->where('transaction_id', 'like', '%' . $request->input('transaction_id') . '%');
        }

        if ($request->filled('receipt_no')) {
            $query->where('receipt_no', 'like', '%' . $request->input('receipt_no') . '%');
        }

        if ($request->filled('payment_date_from') && $request->filled('payment_date_to')) {
            $query->whereBetween('created_at', [
                $request->input('payment_date_from') . ' 00:00:00',
                $request->input('payment_date_to') . ' 23:59:59'
            ]);
        }

        // Count total records
        $totalRecords = Payment::count();
        $filteredRecords = $query->count();

        // Get paginated data
        $payments = $query->orderBy($columnName, $columnSortOrder)
            ->offset($start)
            ->limit($length)
            ->get();

        // Format data for DataTable
        $data = [];
        foreach ($payments as $payment) {
            $data[] = [
                'id' => $payment->id,
                'transaction_id' => $payment->transaction_id,
                'applicant_name' => $payment->applicant_name,
                'amount' => '৳ ' . number_format($payment->amount, 2),
                'status' => '<span class="badge badge-' . $this->getStatusBadgeClass($payment->transaction_status) . '">' . ucfirst($payment->transaction_status) . '</span>',
                'created_at' => $payment->created_at->format('Y-m-d H:i'),
                'applicant_contact' => $payment->applicant_contact,
                'receipt_no' => $payment->receipt_no,
                'action' => $this->getPaymentActions($payment->transaction_id)
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    private function getStatusBadgeClass($status)
    {
        // Last Modified: 2026-07-06
        // Developed By: Streams Tech Ltd.
        // Description: Return badge class based on payment status

        return match($status) {
            'Paid' => 'success',
            'Failed' => 'danger',
            'Canceled' => 'warning',
            'Pending' => 'info',
            'Cash' => 'success',
            default => 'secondary'
        };
    }

    private function getPaymentActions($transactionId)
    {
        // Last Modified: 2026-07-06
        // Developed By: Streams Tech Ltd.
        // Description: Generate action buttons for payment records

        $viewReceiptUrl = route('payment.receipt', $transactionId);
        $downloadReceiptUrl = route('payment.download-receipt', $transactionId);

        return '
            <a href="' . $viewReceiptUrl . '" class="btn btn-sm btn-info" title="View Receipt">
                <i class="fas fa-eye"></i>
            </a>
            <a href="' . $downloadReceiptUrl . '" class="btn btn-sm btn-primary" title="Download Receipt">
                <i class="fas fa-download"></i>
            </a>
        ';
    }
}