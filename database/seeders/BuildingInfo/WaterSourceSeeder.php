<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\WaterSource;
use DB;

class WaterSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [1, 'Tube Well'],
            [2, 'Jar Water'],
            [3, 'Rain Water'],
            [4, 'Others'],
            [5, 'Municipal/Public Water Supply'],
            [6, 'Deep Boring']

        );

     foreach ($types as $type) {

        $existWaterSource =  WaterSource::where('id', $type[0])->first();
        if($existWaterSource)
        {

        }
        else
        {
            $existWaterSource = new WaterSource;
            $existWaterSource->id = $type[0];
        }
        $existWaterSource->source = $type[1];
        $existWaterSource->save();
    }
    }
}



