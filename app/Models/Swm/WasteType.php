<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class WasteType extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.waste_types';

    protected $fillable = [
        'name',
    ];
}
