<?php
// Last Modified: 2026-07-05
// Developed By: Streams Tech Ltd.
// Description: Handles payment checkout, receipt generation, and payment status pages

namespace App\Http\Controllers;

use App\Models\Fsm\Application;
use App\Models\Payment;
use Streamstech\Ekpay\Customer;
use Streamstech\Ekpay\EkpayService;
use Streamstech\Ekpay\Transaction;

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

    public function receipt($id)
    {
        $payment = Payment::findOrFail($id);
        return view('payment.receipt', compact('payment'));
    }
}