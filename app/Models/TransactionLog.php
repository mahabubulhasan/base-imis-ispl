<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{
    use HasFactory;

    protected $table = 'public.transaction_logs';

    protected $fillable = [
        'transaction_id',
        'transaction_date',
        'response',
    ];

    public static function logTransaction($transactionId, $transactionDate, $response)
    {
        return self::create([
            'transaction_id' => $transactionId,
            'transaction_date' => $transactionDate,
            'response' => $response,
        ]);
    }
}
