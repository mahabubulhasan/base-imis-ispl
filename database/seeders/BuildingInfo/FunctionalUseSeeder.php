<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\FunctionalUse;
use DB;

class FunctionalUseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $names = array(
            [1, 'Residential'],
            [2, 'Mixed (Residential, Commercial, Office uses)'],
            [3, 'Educational'],
            [4, 'Health Institution'],
            [5, 'Commercial'],
            [6, 'Industrial'],
            [7, 'Undefined'],
            [8, 'Assembly'],
            [9, 'Business (Offices)'],
            [10, 'Hazardous Building'],
            [11, 'Institution for Care'],
            [12, 'Utility (Auxiliary)'],
            [13, 'Storage Buildings'],
            [14, 'Miscellaneous'],
            [15, 'Garage'],
            [16,'Toilet'],
        );

        foreach ($names as $name) {

            $existFunctionalUse =  DB::table('building_info.functional_uses')
                ->where('name', $name[1])
                ->first();
            if (!$existFunctionalUse) {
                FunctionalUse::create([
                    'id' => $name[0],
                    'name' => $name[1],
                ]);
            }
        }
    }
}
