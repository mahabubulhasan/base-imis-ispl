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

/**
 * @property string|null $sub_location
 * @property string|null $father_or_husband_name
 * @property string|null $road_no
 * @property string|null $road_name
 * @property string $status
 */
class Household extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $revisionCreationsEnabled = true;

    protected $table = 'building_info.households';

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
    ];

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

    public function wasteBins()
    {
        return $this->hasMany(WasteBin::class, 'household_id');
    }

    public function billCollectionPayments()
    {
        return $this->hasMany(BillCollectionPayment::class, 'household_id');
    }

    /** @return array<string, string> */
    public static function statusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_INACTIVE => __('Inactive'),
        ];
    }

    public function isActiveStatus(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeActiveStatus($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
