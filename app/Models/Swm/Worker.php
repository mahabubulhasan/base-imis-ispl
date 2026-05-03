<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Worker extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.workers';

    protected $fillable = [
        'organization_id',
        'work_type_id',
        'name',
        'mobile',
        'email',
        'worker_id_no',
        'age',
        'gender',
        'service_area',
        'employment_type',
        'status',
        'department',
        'supervisor_name',
        'total_work_experience_years',
        'organization_work_experience_years',
        'education_level',
        'education_level_other',
        'employee_id',
        'national_id_no',
    ];

    protected $casts = [
        'age' => 'integer',
        'total_work_experience_years' => 'decimal:2',
        'organization_work_experience_years' => 'decimal:2',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }

    public function vehiclesAsDriver()
    {
        return $this->hasMany(Vehicle::class, 'driver_worker_id', 'id');
    }
}
