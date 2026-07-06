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
                Payment::updateTransactionStatus($trnxId, "paid");
                break;
            case 1021:
                Payment::updateTransactionStatus($trnxId, "failed");
                break;
            case 1022:
                Payment::updateTransactionStatus($trnxId, "canceled");
                break;
        }

        return response()->json([
            'ack_code' => 'ack'.time(),
            'ack_msg' => 'Acknowledge Successfully.',
            'ack_timestamp' => now()->toDateTimeString()
        ]);
    }
}