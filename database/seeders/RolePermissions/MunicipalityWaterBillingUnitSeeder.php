<?php

namespace Database\Seeders\RolePermissions;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class MunicipalityWaterBillingUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            [
                'name' => 'Municipality - Water Billing Unit',
            ],
        ];
        foreach ($roles as $role){
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name){
                case 'Municipality - Water Billing Unit':

                    
                    // Water Supply ISS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Water Supply ISS']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIN('name',['Roads Map Layer','Sewers Line Map Layer','Drains Map Layer','WaterSupply Network Map Layer','Places Map Layer', 'Buildings Map Layer','Water Payment Status Map Layer','Wards Map Layer', 'Summarized Grids Map Layer', 'Water Body Map Layer', 'Land Use Map Layer',
                    'General Map Tools','Water Payment Status Map Tools','Data Export Map Tools','Export Data Map Tools','Owner Information Map Tools','Decision Map Tools','Summary Information Buffer Map Tools','Summary Information Water Bodies Map Tools','Summary Information Wards Map Tools','Summary Information Road Map Tools','Summary Information Point Map Tools','Export in General Map Tools','Export in Decision Map Tools','Export in Summary Information Map Tools',
                        'List Water Supply Collection','Buildings to Road Map Tools',
                        'Import Water Supply Collection From CSV','Building by Structure Map Tools','Water Payment Status Map Tool','Filter by Wards Map Tools',
                        'Export Water Supply Collection Info','Low Income Community Map Layer','Area Population Map Tools','Info Map Tools',
                        'Wards Map Layer', 'Summarized Grids Map Layer', 'List Building Structures', 'View Building Structure', 'Export Building Structures','Municipality Map Layer','Ward Boundary Map Layer'])

            );
                   $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])
                   ->whereIn('name', [
                        'Distribution of Water Supply Payment Dues Chart',
                        'Distribution of Water Supply Services by Ward Chart'           
               ])
               );
                   $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Dashboard'])
                   ->whereIn('name', [
                        'Building Use Composition Chart',
                        'Ward-Wise Distribution of Buildings Chart',
                        'Building CountBox',
                    
               ])
               );
                    break;
            }
        }
    }
}
