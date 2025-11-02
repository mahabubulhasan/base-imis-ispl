<?php

namespace Database\Seeders\Fsm;

use Illuminate\Database\Seeder;
use App\Models\Fsm\ContainmentType;
use DB;

class ContainmentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = array(
            [ 1 , 'Septic Tank connected to Sewer Network' , 3 , true , 'Septic Tank' ],
            [ 2 , 'Septic Tank connected to Drain Network' , 3 , true , 'Septic Tank' ],
            [ 3 , 'Septic Tank connected to Soak Pit' , 3 , true , 'Septic Tank' ],
            [ 4 , 'Septic Tank connected to Water Body' , 3 , true , 'Septic Tank' ],
            [ 5 , 'Septic Tank connected to Open Ground' , 3 , true , 'Septic Tank' ],
            [ 6 , 'Septic Tank connected to No Outlet Connection' , 3 , true , 'Septic Tank' ],
            [ 7 , 'Septic Tank connected to Unknown', 3 , true , 'Septic Tank' ],
            [ 8 , ' Pit/Holding Tank connected to Double Pit' , 4 , true , 'Pit/Holding Tank' ],
            [ 9 , 'Permeable/ Unlined Pit' , 4 , true , 'Pit/Holding Tank'],
            [ 10 , 'Pit/Holding Tank connected to Soak Pit' , 4 , true , 'Pit/Holding Tank'],
            [ 11 , 'Pit/Holding Tank connected to Water Body' , 4 , true , 'Pit/Holding Tank'],
            [ 12 , 'Pit/Holding Tank connected to Open Ground' , 4 , true , 'Pit/Holding Tank'],
            [ 13 , 'Pit/Holding Tank connected to Sewer Network' , 4 , true , 'Pit/Holding Tank'],
            [ 14 , 'Pit/Holding Tank connected to Drain Network' , 4 , true , 'Pit/Holding Tank'],
            [ 15 , 'Pit/Holding Tank connected to No Outlet Connection ' , 4 , true , 'Pit/Holding Tank'],
            [ 16 , 'Pit/Holding Tank connected to Unknown' , 4 , true , 'Pit/Holding Tank'],
            [ 17 , 'Lined Pit with Impermeable Walls and Open Bottom' , 4 , true , 'Pit/Holding Tank'],
            [ 18 , 'Septic Tank connected to Double Pit' , 3 , true , 'Septic Tank']
        );

     foreach ($types as $type) {

         $existStructureType =  DB::table('fsm.containment_types')
                 ->where('type', $type[1])
                 ->first();
         if(!$existStructureType) {
            ContainmentType::insert([
             'id' => $type[0],
             'type' => $type[1],
             'sanitation_system_id' => $type[2],
             'dashboard_display' => $type[3],
             'map_display' => $type[4],
         ]);
         }
     }

    }
}
