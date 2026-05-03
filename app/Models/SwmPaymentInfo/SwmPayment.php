<?php

namespace App\Models\SwmPaymentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwmPayment extends Model
{
    use HasFactory;

    protected $table = 'swm_info.swmservice_payments';
    protected $fillable = [
        'swm_customer_id',
        'customer_name',
        'customer_contact',
        'last_payment_date',
        'receipt_no',
    ];

    public static function selectAll()
    {
        return SwmPayment::select('swm_customer_id', 'customer_name', 'customer_contact', 'last_payment_date', 'receipt_no');
    }
}