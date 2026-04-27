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
        'bin',
        'number_of_waste_bins',
        'total_capacity_kg',
    ];

    public function household()
    {
        return $this->belongsTo(Household::class, 'household_id');
    }
}
