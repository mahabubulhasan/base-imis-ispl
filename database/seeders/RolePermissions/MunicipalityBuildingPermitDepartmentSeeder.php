<?php

namespace Database\Seeders\RolePermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MunicipalityBuildingPermitDepartmentSeeder extends Seeder
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
                'name' => 'Municipality - Building Permit Department',
            ]
          
        ];
        foreach ($roles as $role){
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name){
                case 'Municipality - Building Permit Department':

                    // Buildings page permissions (all except history)
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Structures'])->whereNotIn('type',['History']));
                    
                    // Building Survey page permissions (all)
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Building Surveys']));
                      
                    // Low Income Community page permissions (all except history)
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Low Income Communities'])->whereNotIn('type',['History']));
                    
                    // Dashboard permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])->whereIn('name',['Building Connections to Sanitation System Types Chart','Utility CountBox']));

                    // Containments Page permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Containments'])->whereNotIn('type',['History', 'Service History']));

                    // Building Dashboard permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Dashboard'])->whereIn('name',['Building Use Composition Chart',
                    'Ward-Wise Distribution of Buildings Chart','Sanitation CountBox','Building CountBox']));

                    // FSM Dashboard permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['FSM Dashboard'])->whereIn('name',['Containment Types Categorized by Land Use Chart',
                    'Containment Types Categorized by Building Usage Chart','Ward-Wise Distribution of Containment Types in Residential Buildings Chart','Ward-Wise Distribution of Containment Types Chart','Proportion of Different Containment Types Chart']));

                    // sewer,water supply, drains permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Roads','Sewers','Drain','WaterSupply Network'])
                    ->whereIn('type',['List','View']));

                    // roads permissions
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Roads'])
                    ->whereIn('type',['List','View','View on map']));

                    // Export data
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])->whereIn('name',['Data Export Map Tools']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Data Export'])->whereIn('type',['Export']));

                    // map tools data
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])->whereIn('name',[
                        'Municipality Map Layer',
                        'Ward Boundary Map Layer',
                        'Buildings Map Layer',
                    'Containments Map Layer',
                    'Sanitation System Map Layer',
                    'Wards Map Layer',
                    'PT/CT Toilets Map Layer',
                    'Summarized Grids Map Layer',
                    'Water Body Map Layer',
                    'Land Use Map Layer',
                    'Places Map Layer',
                    'Roads Map Layer',
                    'Sewers Line Map Layer',
                    'Drains Map Layer',
                    'WaterSupply Network Map Layer',
                    'Low Income Community Map Layer',
                    'Decision Map Tools',
                    'Export in Decision Map Tools',
                    'Sewer Potential Map Tools',
                    'Buildings to Sewer Map Tools',
                    'Buildings to Road Map Tools',
                    'Hard to Reach Buildings Map Tools',
                    'Building Close to Water Bodies Map Tools',
                    'Community Toilets Map Tools',
                    'Area Population Map Tools',
                    'Summary Information Buffer Map Tools',
                    'Summary Information Water Bodies Map Tools',
                    'Summary Information Wards Map Tools',
                    'Summary Information Road Map Tools',
                    'Summary Information Point Map Tools',
                    'Export in Summary Information Map Tools',
                    'Data Export Map Tools',
                    'Filter by Wards Map Tools',
                    'Export Data Map Tools',
                    'Export in General Map Tools',
                    'Owner Information Map Tools',
                    'Info Map Tools', 'Containments Emptied Info Map Tools', 'Toilet Isochrone Map Tools', 'Export Containment Report'
                    ]));
                 
                    break;

            }
        }
    }
}
