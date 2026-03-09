<?php

namespace App\Models\Fsm;

use Illuminate\Database\Eloquent\Model;

class PendingApplication extends Model
{
    protected $table = 'fsm.pending_applications';

    protected $fillable = [
        'tax_id',
        'customer_name',
        'customer_contact',
        'holding_owner_name',
        'ward',
        'road_code',
        'address',
        'proposed_emptying_date',
        'notes',
        'is_approved'
    ];
}
