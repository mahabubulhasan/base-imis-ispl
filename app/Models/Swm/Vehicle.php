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
        'operational_type_other',
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

    /**
     * Payload for landfill log / attendance vehicle-context APIs (vehicle + default dumping landfill + waste types).
     *
     * @return array<string, mixed>
     */
    public function toLandfillLogVehicleContextPayload(): array
    {
        $this->loadMissing(['vehicleType', 'driver', 'dumpingLandfill']);
        $landfill = $this->dumpingLandfill;
        $wasteTypes = $landfill ? $landfill->wasteTypes() : collect();

        return [
            'vehicle_type_id' => $this->vehicle_type_id,
            'vehicle_type_name' => $this->vehicleType?->name ?? '',
            'driver_name' => $this->driver?->name ?? '',
            'capacity' => $this->capacity,
            'landfill_id' => $landfill?->id,
            'landfill_name' => $landfill?->name ?? '',
            'waste_type_ids' => $wasteTypes->pluck('id')->values()->all(),
            'waste_types' => $wasteTypes->map(fn ($t) => ['id' => $t->id, 'name' => $t->name])->values()->all(),
        ];
    }
}
