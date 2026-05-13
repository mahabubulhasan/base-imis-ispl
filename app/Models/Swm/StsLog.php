<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class StsLog extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PENDING = 'pending';

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.sts_logs';

    protected $fillable = [
        'vehicle_id',
        'vehicle_type_id',
        'vehicle_type_name',
        'driver_worker_id',
        'driver_name',
        'sts_id',
        'sts_name',
        'waste_type_id',
        'waste_type_name',
        'quantity_ton',
        'source_wards',
        'entry_at',
        'operation_date',
        'operation_status',
        'remarks',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'operation_date' => 'date',
        'quantity_ton' => 'decimal:3',
        'source_wards' => 'array',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function driver()
    {
        return $this->belongsTo(Worker::class, 'driver_worker_id');
    }

    public function sts()
    {
        return $this->belongsTo(Sts::class, 'sts_id');
    }

    public function wasteType()
    {
        return $this->belongsTo(WasteType::class, 'waste_type_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_PENDING => __('Pending'),
        ];
    }
}
