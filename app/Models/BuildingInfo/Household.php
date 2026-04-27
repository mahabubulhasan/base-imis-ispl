<?php

namespace App\Models\BuildingInfo;

use App\Models\LayerInfo\Lic;
use App\Models\Swm\BillCollectionPayment;
use App\Models\Swm\WasteBin;
use App\Models\Swm\Worker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Household extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'building_info.households';

    protected $casts = [
        'is_owner' => 'boolean',
        'is_lic' => 'boolean',
        'segregation_practiced' => 'boolean',
        'waste_bin_provided' => 'boolean',
        'using_this_service_since' => 'date',
        'survey_date' => 'date',
        'waste_charge' => 'decimal:2',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'bin', 'bin');
    }

    public function vanPuller()
    {
        return $this->belongsTo(Worker::class, 'van_puller_id');
    }

    public function lic()
    {
        return $this->belongsTo(Lic::class, 'lic_id', 'id');
    }

    public function wasteBin()
    {
        return $this->hasOne(WasteBin::class, 'household_id');
    }

    public function billCollectionPayments()
    {
        return $this->hasMany(BillCollectionPayment::class, 'household_id');
    }
}
