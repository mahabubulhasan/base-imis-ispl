<?php

namespace App\Models\TaxPaymentInfo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxPayment extends Model
{
    use HasFactory;

    protected $table = 'taxpayment_info.tax_payments';
    protected $fillable = [
        'tax_code', 'owner_name', 'owner_contact', 'last_payment_date'
    ];

    public static function selectAll(){
        return TaxPayment::select('tax_code', 'owner_name', 'owner_contact', 'last_payment_date');
    }
}