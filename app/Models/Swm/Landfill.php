<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Landfill extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.landfills';

    protected $casts = [
        'segregation_practiced' => 'boolean',
        'reuse_practiced' => 'boolean',
        'treatment' => 'boolean',
    ];

    public function sts()
    {
        return $this->hasMany(Sts::class, 'destination_landfill_id', 'id');
    }
}
