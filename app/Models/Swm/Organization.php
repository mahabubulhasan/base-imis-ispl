<?php

namespace App\Models\Swm;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Organization extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.organizations';

    protected $primaryKey = 'id';

    protected $casts = [
        'status' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'swm_organization_id', 'id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class, 'organization_id', 'id');
    }

    public function scopeOperational($query)
    {
        return $query->where('status', true);
    }
}
