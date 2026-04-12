<?php

namespace Database\Seeders\Swm;

use App\Models\Swm\WorkType;
use Illuminate\Database\Seeder;

/**
 * Idempotent default work types. Matches only non–soft-deleted rows (default WorkType query);
 * if a name exists only on a trashed row, a new active row with the same name may be created.
 */
class WorkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Household level collector',
            'Street sweeper',
            'Drain cleaner',
        ];

        foreach ($names as $name) {
            WorkType::firstOrCreate(['name' => $name]);
        }
    }
}
