<?php

namespace App\Models\Swm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Venturecraft\Revisionable\RevisionableTrait;

class BillCollectionPayment extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.bill_collection_payments';

    protected $casts = [
        'payment_for_month' => 'date',
        'payment_time' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected $appends = [
        'receipt_copy_url',
    ];

    public function primaryCollectionSite()
    {
        return $this->belongsTo(PrimaryCollectionSite::class, 'primary_collection_site_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function getReceiptCopyUrlAttribute(): ?string
    {
        if (empty($this->receipt_copy_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_copy_path);
    }
}
