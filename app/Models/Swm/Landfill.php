<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Landfill extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.landfills';

    protected $fillable = [
        'landfill_id',
        'name',
        'location',
        'operator_name',
        'contact_number',
        'capacity',
        'area',
        'landfill_type_id',
        'source_sts_ids',
        'source_wards',
        'segregation_practiced',
        'reuse_practiced',
        'waste_type_ids',
        'weighbridge_facility_available',
        'boundary_wall_available',
        'lighting_arrangement_available',
        'manpower_deployed',
        'adequate_covering_arrangement_available',
        'gas_control_system_available',
        'leachate_collection_system_available',
        'treatment',
        'operational_status',
    ];

    protected $casts = [
        'source_sts_ids' => 'array',
        'source_wards' => 'array',
        'landfill_type_id' => 'integer',
        'segregation_practiced' => 'boolean',
        'reuse_practiced' => 'boolean',
        'waste_type_ids' => 'array',
        'weighbridge_facility_available' => 'boolean',
        'boundary_wall_available' => 'boolean',
        'lighting_arrangement_available' => 'boolean',
        'manpower_deployed' => 'integer',
        'adequate_covering_arrangement_available' => 'boolean',
        'gas_control_system_available' => 'boolean',
        'leachate_collection_system_available' => 'boolean',
        'treatment' => 'boolean',
        'area' => 'decimal:2',
    ];

    public function landfillType()
    {
        return $this->belongsTo(LandfillType::class, 'landfill_type_id');
    }

    public function sts()
    {
        return $this->hasMany(Sts::class, 'destination_landfill_id', 'id');
    }

    public function sourceSts(): Collection
    {
        $ids = $this->source_sts_ids ?? [];
        if (empty($ids)) {
            return new Collection();
        }

        return Sts::query()
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function wasteTypes(): Collection
    {
        $ids = $this->waste_type_ids ?? [];
        if (empty($ids)) {
            return new Collection();
        }

        return WasteType::query()
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public static function getNextSerial(): string
    {
        $latest = self::withTrashed()
            ->where('landfill_id', 'like', 'LF%')
            ->orderByDesc('landfill_id')
            ->first();

        if (! $latest || ! preg_match('/^LF(\d+)$/', (string) $latest->landfill_id, $matches)) {
            return '0001';
        }

        $next = intval($matches[1]) + 1;

        return str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public static function generateLandfillId(string $serial): string
    {
        return 'LF'.str_pad($serial, 4, '0', STR_PAD_LEFT);
    }

    protected static function booted(): void
    {
        static::creating(function (Landfill $landfill) {
            if (empty($landfill->landfill_id)) {
                $serial = self::getNextSerial();
                $landfill->landfill_id = self::generateLandfillId($serial);
            }
        });
    }
}
