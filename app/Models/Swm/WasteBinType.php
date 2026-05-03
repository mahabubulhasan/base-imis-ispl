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

    public const OTHERS_SPECIFY_NAME = 'Others (specify)';

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.waste_bin_types';

    public function wasteBins()
    {
        return $this->hasMany(WasteBin::class, 'waste_bin_type_id', 'id');
    }
}
