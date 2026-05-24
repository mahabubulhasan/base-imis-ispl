<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class OrganizationType extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    /** Seeded organization type IDs (insert order in migration). */
    public const ID_PRIVATE = 1;

    public const ID_GOVERNMENT = 2;

    public const ID_OTHER = 3;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.organization_types';

    protected $fillable = [
        'name',
        'description',
    ];

    public static function seededIds(): array
    {
        return [
            self::ID_PRIVATE,
            self::ID_GOVERNMENT,
            self::ID_OTHER,
        ];
    }

    public static function isSeeded(?int $id): bool
    {
        return $id !== null && in_array($id, self::seededIds(), true);
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'organization_type_id', 'id');
    }
}
