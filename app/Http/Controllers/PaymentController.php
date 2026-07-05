<?php
// Last Modified: 2026-07-05
// Developed By: Streams Tech Ltd.
// Description: Handles payment checkout, receipt generation, and payment status pages

namespace App\Http\Controllers;

use App\Models\Payment;

class PaymentController extends Controller
{
    public function checkout()
    {
        return view('payment.checkout');
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