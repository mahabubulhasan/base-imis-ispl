<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class WasteBinType extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.waste_bin_types';

    protected $fillable = [
        'name',
    ];

    public function wasteBins()
    {
        return $this->hasMany(WasteBin::class, 'waste_bin_type_id', 'id');
    }
}
