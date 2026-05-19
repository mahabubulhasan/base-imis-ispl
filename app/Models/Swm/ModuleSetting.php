<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Model;

class ModuleSetting extends Model
{
    protected $table = 'swm.module_settings';

    protected $fillable = [
        'per_capita_sw_generation_kg_per_day',
        'description',
    ];

    protected $casts = [
        'per_capita_sw_generation_kg_per_day' => 'decimal:2',
    ];
}
