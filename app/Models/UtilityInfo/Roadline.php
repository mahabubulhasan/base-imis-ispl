<?php
// Last Modified: 2026-04-24
// Developed By: Streams Tech Ltd.
// Description: Roadline model for utility road records and related helper methods.

namespace App\Models\UtilityInfo;

use App\Models\BuildingInfo\Building;
use App\Models\Fsm\Application;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\UtilityInfo\SewerLine;
use App\Models\UtilityInfo\Drains;
use App\Models\UtilityInfo\WaterSupplys;
use App\Jobs\RunTopologyUpdate;
use Illuminate\Support\Facades\DB;

class Roadline extends Model
{
    use HasFactory;
    use RevisionableTrait;
    use SoftDeletes;
    protected $revisionCreationsEnabled = true;
    protected $table = 'utility_info.roads';
    protected $primaryKey = 'code';
    public $incrementing = false;

    /**
     * Get the buildings associated with the road.
     *
     *
     * @return HasMany
     */
    public function buildings(){
        return $this->hasMany(Building::class,'road_code','code');
    }


    /**
     * Get the sewer associated with the road.
     *
     *
     * @return HasMany
     */
    public function sewers(){
        return $this->hasMany(SewerLine::class,'road_code','code');
    }


    /**
     * Get the sewer associated with the road.
     *
     *
     * @return HasMany
     */
    public function drains(){
        return $this->hasMany(Drain::class,'road_code','code');
    }

    /**
     * Get the sewer associated with the road.
     *
     *
     * @return HasMany
     */
    public function water_supply(){
        return $this->hasMany(WaterSupplys::class,'road_code','code');
    }

    /**
     * Get the next serial number for a given ward (Municipality Road).
     * Serial is based on the latest road_uid for that ward.
     * road_uid format: 20512510 + Ward(2 digits) + Serial(4 digits)
     * Example: 20512510010001, 20512510010002, etc.
     *
     * @param int $ward Ward number
     * @return string Next serial padded to 4 digits (e.g., '0001', '0002', '0042')
     */
    public static function getNextSerialForWard($ward)
    {
        // Query latest serial using RIGHT(road_uid, 4) ordering.
        $latestRoad = DB::table('utility_info.roads')
            ->selectRaw('right(road_uid, 4) as max')
            ->whereRaw('length(road_uid) = 14')
            ->orderByDesc('max')
            ->first();

        // If no roads exist for this ward, start with serial 0001
        if (!$latestRoad || !$latestRoad->max) {
            return '0001';
        }

        $nextSerial = intval($latestRoad->max) + 1;

        // Pad the next serial to 4 digits
        return str_pad($nextSerial, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get the full auto-generated road code for a given ward and serial.
     * Format: 20512510 + Ward(2 digits) + Serial(4 digits)
     *
     * @param int $ward Ward number
     * @param string|int $serial Serial number (4 digits or will be padded)
     * @return string Full road code (e.g., '20512510010001')
     */
    public static function generateMunicipalityRoadCode($ward, $serial)
    {
        $paddedWard = str_pad($ward, 2, '0', STR_PAD_LEFT);
        $paddedSerial = str_pad($serial, 4, '0', STR_PAD_LEFT);

        return '20512510' . $paddedWard . $paddedSerial;
    }


 protected static function booted()
    {
        // todo disabled temporarily on 25 feb 2026
        /* static::saved(function ($road) {
            // Run the function in background
            RunTopologyUpdate::dispatch($road->code);
        }); */
    }


}
