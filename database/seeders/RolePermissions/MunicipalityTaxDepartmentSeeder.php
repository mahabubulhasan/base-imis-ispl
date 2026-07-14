<?php

namespace Database\Seeders\RolePermissions;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class MunicipalityTaxDepartmentSeeder extends Seeder
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
                'name' => 'Municipality - Tax Department',
            ],
        ];
        foreach ($roles as $role){
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name){
                case 'Municipality - Tax Department':

                    
                    // Property Tax Collection ISS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Property Tax Collection ISS']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIN('name',[
                        'Municipality Map Layer',
                        'Ward Boundary Map Layer',
                        'Buildings Map Layer',
                        'Wards Map Layer',
                        'Summarized Grids Map Layer',
                        'Tax Payment Status Buildings Map Layer', 
                        'Water Body Map Layer', 
                        'Land Use Map Layer', 
                        'Places Map Layer', 
                        'Roads Map Layer',
                        'Sewers Line Map Layer',
                        'Drains Map Layer',
                        'WaterSupply Network Map Layer', 
                        'Low Income Community Map Layer',
                        'General Map Tools',
                        'Building by Structure Map Tools',
                        'Property Tax Map Tools',
                        'Export in General Map Tools',
                        'Decision Map Tools',
                        'Tax Due Map Tools',
                        'Buildings to Road Map Tools',
                        'Summary Information Buffer Map Tools',
                        'Summary Information Water Bodies Map Tools',
                        'Summary Information Wards Map Tools',
                        'Summary Information Road Map Tools',
                        'Summary Information Point Map Tools',
                        'Export in Decision Map Tools',
                        'Export in Summary Information Map Tools',
                        'Data Export Map Tools',
                        'Filter by Wards Map Tools',
                        'Export Data Map Tools',
                        'Area Population Map Tools',
                        'Owner Information Map Tools',
                        'Info Map Tools',
                    ]));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])->whereIn('name', ['Property Tax Payment Chart']));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Dashboard'])
                    ->whereIn('name', [
                        'Building Use Composition Chart',
                        'Ward-Wise Distribution of Buildings Chart',
                        'Building CountBox'
                    ]));
                break;
            }
        }
    }
}
