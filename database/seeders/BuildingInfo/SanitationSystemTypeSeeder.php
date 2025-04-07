<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\SanitationSystem;
use DB;

class SanitationSystemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =  array(
            [ 1 , 'Sewer Network' , True , True , 'sewers.svg' ],
            [ 2 , 'Drain Network' , False , True , 'others.svg' ],
            [ 3 , 'Septic Tank' , True , True , 'septic-tank.svg' ],
            [ 4 , 'Pit/ Holding Tank' , True , True , 'pit.svg' ],
            [ 5 , 'Onsite Treatment (Anaerobic Digestor/ Biogas, DEWATS, etc.)' , True , True , 'no_icon' ],
            [ 6 , 'Composting Toilets (Ecosan, UDDT, etc.)' , True , True , 'composting-toilet.svg' ],
            [ 7 , 'Water Body' , False , True , 'others.svg' ],
            [ 8 , 'Open Ground' , False , True , 'others.svg' ],
            [ 9 , 'Community Toilet' , False , True , 'others.svg' ],
            [ 10 , 'Open Defecation' , False , True , 'others.svg' ],
            [ 11 , 'Shared Containment' , False , False , ''],
            [ 12 , 'Others' , True , True , '']
        );

     foreach ($types as $type) {

         $existSanitationSystemType =  DB::table('building_info.sanitation_systems')
                 ->where('sanitation_system', $type[1])
                 ->first();
         if(!$existSanitationSystemType) {
            SanitationSystem::create([
             'id' => $type[0],
             'sanitation_system' => $type[1],
             'dashboard_display' => $type[2],
             'map_display' => $type[3],
             'icon_name' => $type[4],
         ]);
         }
     }

    }
}
