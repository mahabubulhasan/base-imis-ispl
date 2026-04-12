<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Sts extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.sts';

    protected $casts = [
        'segregation_practiced' => 'boolean',
    ];

    public function landfill()
    {
        return $this->belongsTo(Landfill::class, 'destination_landfill_id');
    }
}
