<?php

namespace App\Models\Swm;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Sts extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;

    protected $revisionCreationsEnabled = true;

    protected $table = 'swm.sts';

    protected $fillable = [
        'sts_id',
        'name',
        'location',
        'ward_no',
        'road_id',
        'road_name',
        'latitude',
        'longitude',
        'operator_name',
        'contact_number',
        'capacity',
        'area',
        'source_wards',
        'segregation_practiced',
        'waste_type_ids',
        'destination_landfill_id',
        'operational_status',
    ];

    protected $casts = [
        'segregation_practiced' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'area' => 'decimal:2',
        'source_wards' => 'array',
        'waste_type_ids' => 'array',
    ];

    public function landfill()
    {
        return $this->belongsTo(Landfill::class, 'destination_landfill_id');
    }

    /**
     * Resolve waste types referenced via the JSON array.
     */
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

    /**
     * Get the next serial number for a given ward.
     * Serial is based on the latest sts_id for that ward.
     * sts_id format: STS-{Ward(2 digits)}-{Serial(4 digits)}
     * Example: STS-01-0001, STS-01-0002, etc.
     */
    public static function getNextSerialForWard(int $ward): string
    {
        $padded = str_pad((string) $ward, 2, '0', STR_PAD_LEFT);

        $latest = self::withTrashed()
            ->where('sts_id', 'like', "STS-{$padded}-%")
            ->orderByDesc('sts_id')
            ->first();

        if (! $latest || ! $latest->sts_id) {
            return '0001';
        }

        $currentSerial = substr($latest->sts_id, -4);
        $nextSerial = intval($currentSerial) + 1;

        return str_pad((string) $nextSerial, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the full auto-generated sts_id for a given ward and serial.
     * Format: STS-{Ward(2 digits)}-{Serial(4 digits)}
     */
    public static function generateStsId(int $ward, string $serial): string
    {
        $paddedWard = str_pad((string) $ward, 2, '0', STR_PAD_LEFT);
        $paddedSerial = str_pad((string) $serial, 4, '0', STR_PAD_LEFT);

        return "STS-{$paddedWard}-{$paddedSerial}";
    }

    protected static function booted(): void
    {
        static::creating(function (Sts $sts) {
            if (empty($sts->sts_id) && ! empty($sts->ward_no)) {
                $serial = self::getNextSerialForWard((int) $sts->ward_no);
                $sts->sts_id = self::generateStsId((int) $sts->ward_no, $serial);
            }
        });
    }
}
