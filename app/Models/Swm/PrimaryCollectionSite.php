<?php

namespace App\Models\Swm;

use App\Models\BuildingInfo\Building;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class PrimaryCollectionSite extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.primary_collection_sites';

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
        return $this->belongsTo(Lic::class, 'lic_id', 'lic_id');
    }

    public function billCollectionPayments()
    {
        return $this->hasMany(BillCollectionPayment::class, 'primary_collection_site_id');
    }
}
