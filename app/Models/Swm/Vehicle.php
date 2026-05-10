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

    protected $fillable = [
        'organization_id',
        'vehicle_type_id',
        'vehicle_id_no',
        'vehicle_number',
        'capacity',
        'driver_worker_id',
        'service_area',
        'service_wards',
        'fuel_type',
        'operational_type',
        'vehicle_registration_no',
        'engine_no',
        'chassis_no',
        'status',
        'last_maintenance_year',
        'remarks',
        'dumping_place_kind',
        'dumping_sts_id',
        'dumping_landfill_id',
        'dumping_place_other',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'service_wards' => 'array',
        'last_maintenance_year' => 'integer',
    ];

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
