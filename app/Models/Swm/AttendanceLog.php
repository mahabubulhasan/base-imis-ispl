<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class AttendanceLog extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_ON_LEAVE = 'on_leave';

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.attendance_logs';

    protected $fillable = [
        'organization_id',
        'worker_id',
        'department',
        'work_type_id',
        'work_type_name',
        'supervisor_name',
        'entry_at',
        'attendance_status',
        'check_in_at',
        'check_out_at',
        'remarks',
    ];

    protected $casts = [
        'entry_at' => 'datetime',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function workType()
    {
        return $this->belongsTo(WorkType::class, 'work_type_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PRESENT => __('Present'),
            self::STATUS_ABSENT => __('Absent'),
            self::STATUS_ON_LEAVE => __('On Leave'),
        ];
    }
}
