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

    public const CATEGORY_PRIVATE = 'private';
    public const CATEGORY_GOVERNMENT = 'government';
    public const CATEGORY_OTHER = 'other';

    public const CATEGORY_OPTIONS = [
        self::CATEGORY_PRIVATE => 'Private',
        self::CATEGORY_GOVERNMENT => 'Government',
        self::CATEGORY_OTHER => 'Others',
    ];

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.organizations';

    protected $primaryKey = 'id';

    protected $casts = [
        'status' => 'boolean',
    ];

    public static function categoryOptions(): array
    {
        return array_map(static fn (string $label) => __($label), self::CATEGORY_OPTIONS);
    }

    public function getOrganizationCategoryLabelAttribute(): string
    {
        return __(self::CATEGORY_OPTIONS[$this->organization_category] ?? 'N/A');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'swm_organization_id', 'id');
    }

    public function workers()
    {
        return $this->hasMany(Worker::class, 'organization_id', 'id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'organization_id', 'id');
    }

    public function scopeOperational($query)
    {
        return $query->where('status', true);
    }
}
