<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\TransactionLog;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'public.payments';

    protected $fillable = [
        'receipt_no',
        'transaction_id',
        'application_id',
        'payment_timestamp',
        'amount',
        'applicant_name',
        'applicant_contact',
        'holding_owner_name',
        'address',
        'tax_code',
        'service_type',
        'proposed_service_date',
        'transaction_status' // pending, success, failed, cancel
    ];

    public function transactionLogs()
    {
        return $this->hasMany(TransactionLog::class, 'transaction_id', 'transaction_id')->orderBy('created_at', 'desc');
    }

    public static function generateReceiptNumber()
    {
        $now = now();
        $yearMonth = $now->format('ym'); // YYMM format (e.g., "2607" for July 2026)

        // Find the last payment for this month
        $lastPaymentThisMonth = self::whereRaw("receipt_no LIKE ?", [$yearMonth . '%'])
            ->latest('id')
            ->first();

        if ($lastPaymentThisMonth) {
            // Extract the 4-digit sequence number (last 4 digits)
            $lastSequence = (int) substr($lastPaymentThisMonth->receipt_no, 4);
        } else {
            $lastSequence = 0;
        }

        // Increment and format to 4 digits
        $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);

        return $yearMonth . $newSequence;
    }

    public static function generateTransactionId()
    {
        $lastPayment = self::latest('id')->first();

        if ($lastPayment) {
            // Extract the alphanumeric part after "TX-"
            $lastIdPart = substr($lastPayment->transaction_id, 3);
            // Convert from base36 back to decimal
            $lastNumber = base_convert($lastIdPart, 36, 10);
        } else {
            $lastNumber = 0;
        }

        // Increment the counter
        $newNumber = $lastNumber + 1;

        // Convert to base36 (alphanumeric: 0-9, a-z) and pad to 10 characters
        $newIdPart = str_pad(base_convert($newNumber, 10, 36), 10, '0', STR_PAD_LEFT);

        return 'TX-' . strtoupper($newIdPart);
    }

    public static function createPayment(
        $applicantName,
        $applicantContact,
        $holdingOwnerName,
        $address,
        $taxCode,
        $applicationId = null,
        $proposedServiceDate = null,
        $amount = 1500,
        $serviceType = 'Emptying'
    ) {
        $receiptNo = self::generateReceiptNumber();
        $transactionId = self::generateTransactionId();
        $paymentTimestamp = now();

        return self::create([
            'receipt_no' => $receiptNo,
            'transaction_id' => $transactionId,
            'payment_timestamp' => $paymentTimestamp,
            'amount' => $amount, // Assuming a fixed amount for the service
            'applicant_name' => $applicantName,
            'applicant_contact' => $applicantContact,
            'holding_owner_name' => $holdingOwnerName,
            'address' => $address,
            'tax_code' => $taxCode,
            'service_type' => $serviceType,
            'proposed_service_date' => $proposedServiceDate,
            'application_id' => $applicationId,
            'transaction_status' => 'Pending'
        ]);
    }

    public static function updateTransactionStatus($transactionId, $status)
    {
        $payment = self::where('transaction_id', $transactionId)->first();

        if ($payment) {
            $payment->transaction_status = $status;
            $payment->save();
            return true;
        }

        return false;
    }

    public static function cashInHandPayment($applicantName, $applicantContact, $holdingOwnerName, $address, $taxCode, $applicationId = null, $proposedServiceDate = null, $amount = 1500, $serviceType = 'Emptying')
    {
        // Create a new payment record with status 'Cash' for cash in hand
        return self::create([
            'receipt_no' => self::generateReceiptNumber(),
            'transaction_id' => self::generateTransactionId(),
            'payment_timestamp' => now(),
            'amount' => $amount,
            'applicant_name' => $applicantName,
            'applicant_contact' => $applicantContact,
            'holding_owner_name' => $holdingOwnerName,
            'address' => $address,
            'tax_code' => $taxCode,
            'service_type' => $serviceType,
            'proposed_service_date' => $proposedServiceDate,
            'application_id' => $applicationId,
            'transaction_status' => 'Cash' // Marking as cash in hand
        ]);
    }
}
