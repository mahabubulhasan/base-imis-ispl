<?php

namespace App\Models\Swm;

use App\Models\BuildingInfo\Household;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class WasteBin extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.waste_bins';

    protected $fillable = [
        'household_id',
        'waste_bin_type_id',
        'type_other_detail',
        'placed_at_buildings',
        'sub_location',
        'ward_no',
        'road_no',
        'road_name',
        'latitude',
        'longitude',
        'bin',
        'total_capacity_kg',
    ];

    protected $casts = [
        'placed_at_buildings' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'total_capacity_kg' => 'decimal:2',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }

    public function wasteBinType()
    {
        return $this->belongsTo(WasteBinType::class, 'waste_bin_type_id');
    }
}
