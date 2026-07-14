<?php

namespace Database\Seeders\RolePermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MunicipalityPublicHealthDepartmentSeeder extends Seeder
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
                'name' => 'Municipality - Public Health Department',
            ],
        ];
        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name) {
                case 'Municipality - Public Health Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Low Income Communities'])
                        ->whereIn('type', ['View', 'Export', 'List', 'View on map']));

                    // PT/CT IMS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['PT/CT Toilets'])
                    //     ->whereIn('type', ['View', 'Export', 'List', 'View on map']));


                    // CWIS IMS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['CWIS'])
                    //     ->whereIn('type', ['List','View','Export']));

                    // Water Supply Network module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['WaterSupply Network'])
                    //     ->whereIn('type', ['View', 'List', 'View on map']));
                    // Public Health ISS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Water Samples', 'Hotspots', 'Yearly Waterborne Cases'])->whereNotIn('type', ['History']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Maps'])
                        ->whereIn('name', [
                            'Export Data Map Tools',
                            'Containments Map Layer',
                            'Buildings Map Layer',
                            'Treatment Plants Map Layer',
                            'Roads Map Layer',
                            'Places Map Layer',
                            'Land Use Map Layer',
                            'Wards Map Layer',
                            'Summarized Grids Map Layer',
                            'Sewers Line Map Layer',
                            'Sanitation System Map Layer',
                            'Water Samples Map Layer',
                            'Data Export Map Tools',
                            'Filter by Wards Map Tools',
                            'Export Data Map Tools',
                            'Decision Map Tools',
                            'Building Close to Water Bodies Map Tools',
                            'Area Population Map Tools',
                            'Summary Information Buffer Map Tools',
                            'Summary Information Water Bodies Map Tools',
                            'Summary Information Wards Map Tools',
                            'Summary Information Road Map Tools',
                            'Summary Information Point Map Tools',
                            'Export in Decision Map Tools',
                            'Export in Summary Information Map Tools',
                            'Public Health Map Layer',
                            'Drains Map Layer',
                            'PT/CT Toilets Map Layer',
                            'WaterSupply Network Map Layer',
                            'Ward Boundary Map Layer',
                            'Water Body Map Layer',
                            'Municipality Map Layer',
                            'Community Toilets Map Tools',
                            'Low Income Community Map Layer',
                            'View PT/CT Toilet on Map',

                        ]));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])
                        ->whereIn('name', [
                            'Public Health CountBox',
                            'PTCT CountBox',
                            'Performance of Municipal Treatment Plants by Last 5 Years Chart',
                            'Yearly Distribution of Waterborne Disease Chart',
                            'Building Connections to Sanitation System Types Chart',
                        ]));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Dashboard'])
                        ->whereIn('name', [
                            'Building Use Composition Chart',
                            'Ward-Wise Distribution of Buildings Chart',
                            'Sanitation CountBox',
                            'Building CountBox',
                        ]));
                        
                    break;
            }
        }
    }
}
