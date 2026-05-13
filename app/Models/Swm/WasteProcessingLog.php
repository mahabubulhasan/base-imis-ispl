<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class WasteProcessingLog extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.waste_processing_logs';

    protected $fillable = [
        'entry_at',
        'report_date',
        'reporting_month',
        'waste_received_ton',
        'organic_waste_composted_ton',
        'inorganic_waste_recycled_ton',
        'waste_incinerated_ton',
        'waste_burned_open_air_ton',
        'residual_waste_landfilled_ton',
        'remarks',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'report_date' => 'date',
        'reporting_month' => 'date',
        'waste_received_ton' => 'decimal:2',
        'organic_waste_composted_ton' => 'decimal:2',
        'inorganic_waste_recycled_ton' => 'decimal:2',
        'waste_incinerated_ton' => 'decimal:2',
        'waste_burned_open_air_ton' => 'decimal:2',
        'residual_waste_landfilled_ton' => 'decimal:2',
    ];
}
