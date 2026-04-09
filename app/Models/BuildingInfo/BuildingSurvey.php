<?php

namespace App\Models\BuildingInfo;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use App\Models\BuildingInfo\SanitationSystem;

class BuildingSurvey extends Model
{
    use SoftDeletes;

    use RevisionableTrait;

    protected $revisionCreationsEnabled = true;
    protected $table = 'building_info.building_surveys';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'temp_building_code',
        'tax_code',
        'collected_date',
        'ward',
        'road_code',
        'house_number',
        'functional_use_id',
        'use_category_id',
        'water_source_id',
        'sanitation_system_id',
        'sewer_code',
        'drain_code',
        'kml',
        'user_id',
        'is_enabled',
        'payload_json',
    ];

    protected $casts = [
        'collected_date' => 'date:Y-m-d',
        'payload_json' => 'array',
    ];
}
