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

    public const CODE_GOVERNMENT = 'government';

    public const CODE_PRIVATE = 'private';

    public const CODE_OTHER = 'other';

    public const CODE_OPTIONS = [
        self::CODE_PRIVATE => 'Private',
        self::CODE_GOVERNMENT => 'Government',
        self::CODE_OTHER => 'Others',
    ];

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.organization_types';

    protected $fillable = [
        'name',
        'description',
        'code',
    ];

    public static function codeOptions(): array
    {
        return array_map(static fn (string $label) => __($label), self::CODE_OPTIONS);
    }

    public static function seededCodes(): array
    {
        return [
            self::CODE_PRIVATE,
            self::CODE_GOVERNMENT,
            self::CODE_OTHER,
        ];
    }

    public function organizations()
    {
        return $this->hasMany(Organization::class, 'organization_type_id', 'id');
    }
}
