<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Vehicle extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.vehicles';

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function driver()
    {
        return $this->belongsTo(Worker::class, 'driver_worker_id');
    }

    public function dumpingSts()
    {
        return $this->belongsTo(Sts::class, 'dumping_sts_id');
    }

    public function dumpingLandfill()
    {
        return $this->belongsTo(Landfill::class, 'dumping_landfill_id');
    }
}
