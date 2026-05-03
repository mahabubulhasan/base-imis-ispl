<?php

namespace Database\Seeders\Swm;

use App\Models\Swm\WasteType;
use Illuminate\Database\Seeder;

/**
 * Idempotent default waste types. Matches only non–soft-deleted rows (default WasteType query);
 * if a name exists only on a trashed row, a new active row with the same name may be created.
 */
class WasteTypeSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Municipal Solid Waste',
            'Household Waste',
            'Hazardous Industrial Waste',
            'Medical Waste',
            'Construction Waste',
            'E-Waste',
            'Drain Waste',
            'Mixed',
        ];

        foreach ($names as $name) {
            WasteType::firstOrCreate(['name' => $name]);
        }
    }
}
