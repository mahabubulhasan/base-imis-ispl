<?php

namespace Database\Seeders\BuildingInfo;

use Illuminate\Database\Seeder;
use App\Models\BuildingInfo\UseCategory;
use DB;

class UseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $categorys =  array(
            [ 1 , 'Residential' , 1 ],
            [ 2 , 'Housing' , 1 ],
            [ 3 , 'Apartment' , 1 ],
            [ 4 , 'Orphanage' , 1 ],
            [ 5 , 'Old-aged Home' , 1 ],
            [ 6 , 'Hostel' , 1 ],
            [ 7 , 'Mixed' , 2 ],
            [ 8 , 'School' , 3 ],
            [ 9 , 'College' , 3 ],
            [ 10 , 'University' , 3 ],
            [ 11 , 'Training Center' , 3 ],
            [ 12 , 'Hospital' , 4 ],
            [ 13 , 'Clinic/Health Post' , 4 ],
            [ 14 , 'Shop' , 5 ],
            [ 15 , 'Restaurant' , 5 ],
            [ 16 , 'Hotel / Resort' , 5 ],
            [ 17 , 'Offices (Private)' , 5 ],
            [ 18 , 'Shopping mall / Super Market' , 5 ],
            [ 19 , 'Party Palace/Banquets' , 5 ],
            [ 20 , 'Business Complex' , 5 ],
            [ 21 , 'Industry' , 6 ],
            [ 22 , 'Factory' , 6 ],
            [ 23 , 'Warehouse' , 6 ],
            [ 24 , 'Workshop' , 6 ],
            [ 25 ,  'Printing Press',6],
            [ 26 , 'Agriculture Farm' , 7 ],
            [ 27 , 'LiveStocks' , 7 ],
            [ 28 , 'City hall' , 8 ],
            [ 29 , 'Museum' , 8 ],
            [ 30 , 'Public Library and archive' , 8 ],
            [ 31 , 'Public transportation terminal' , 8 ],
            [ 32 , 'Parking' , 8 ],
            [ 33 , 'Post office' , 8 ],
            [ 34 , 'Community Toilet' , 8 ],
            [ 35 , 'Public Toilet' , 8 ],
            [ 36 , 'Municipal Office' , 9 ],
            [ 37 , 'Ward Office' , 9 ],
            [ 38 , 'Government Office' , 9 ],
            [ 39 , 'Police Office' , 9 ],
            [ 40 , 'Fire Station' , 9 ],
            [ 41 , 'Army barrack' , 9 ],
            [ 42 , 'Jail' , 9 ],
            [ 43 , 'Club' , 10 ],
            [ 44 , 'Stadium' , 10 ],
            [ 45 , 'Cinema/theatre' , 10 ],
            [ 46 , 'Sports complex' , 10 ],
            [ 47 , 'Fitness center' , 10 ],
            [ 48 , 'Recreational center' , 10 ],
            [ 49 , 'NGO' , 11 ],
            [ 50 , 'INGO' , 11 ],
            [ 51 , 'Political Party' , 11 ],
            [ 52 , 'Guthi house' , 11 ],
            [ 53 , 'Media' , 11 ],
            [ 54 , 'Social Group /Samiti Bhawan' , 11 ],
            [ 55 , 'Temple' , 12 ],
            [ 56 , 'Church' , 12 ],
            [ 57 , 'Mosque' , 12 ],
            [ 58 , 'Stupa' , 12 ],
            [ 59 , 'Hermitage (kuti)' , 12 ],
            [ 60 , 'Mourning house' , 12 ],
            [ 61 , 'Bihar/Gumba' , 12 ],
            [ 62 , 'Bhajan Mandal' , 12 ],
            [ 63 , 'Cultural Centers' , 12 ],
            [ 64 , 'Bank' , 13],
            [ 65 , 'Cooperative/Finance' , 13 ],
            [ 66 , 'Vacant building' , 14],
            [ 67 , 'Building under construction' ,14],
        );


     foreach ($categorys as $category) {

         $existUseCategory =  DB::table('building_info.use_categorys')
                 ->where('name', $category[1])
                 ->first();
         if(!$existUseCategory) {
            UseCategory::create([
             'id' => $category[0],
             'name' => $category[1],
             'functional_use_id' => $category[2]
         ]);
         }
     }

    }
}
