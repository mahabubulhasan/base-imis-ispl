<?php

namespace Database\Seeders\RolePermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MunicipalitySolidWasteManagementDepartmentSeeder extends Seeder
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
                'name' => 'Municipality - Solid Waste Management Department',
            ],
        ];
        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name) {
                case 'Municipality - Solid Waste Management Department':

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Swm Service Payment']));

                    // Solid Waste ISS → Service Providers: organizations, work types, and workers (same area in the app).
                    $swmServiceProviderGroups = [
                        'SWM Service Provider Organizations',
                        'SWM Service Provider Work Types',
                        'SWM Service Provider Workers',
                        'SWM Service Provider Vehicle Types',
                        'SWM Service Provider Vehicles',
                    ];
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', $swmServiceProviderGroups));

                    $swmServiceFacilityGroups = [
                        'SWM Service Facility Landfills',
                        'SWM Service Facility STS',
                    ];
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', $swmServiceFacilityGroups));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Maps'])
                        ->whereIn('name', [
                            //Export Tools
                            'Export in General Map Tools',
                            'Export in Decision Map Tools',
                            'Export in Summary Information Map Tools',
                            //Map Layers
                            'Municipality Map Layer',
                            'Ward Boundary Map Layer',
                            'Buildings Map Layer',
                            'Roads Map Layer',
                            'Places Map Layer',
                            'Land Use Map Layer',
                            'Summarized Grids Map Layer',
                            'Water Body Map Layer',
                            'Solid Waste Status Map Layer',
                            'Low Income Community Map Layer',
                            'Wards Map Layer',
                            //Map Tools
                            'General Map Tools',
                            'Building by Structure Map Tools',
                            'Data Export Map Tools',
                            'Filter by Wards Map Tools',
                            'Export Data Map Tools',
                            'Decision Map Tools',
                            'Buildings to Road Map Tools',
                            'Area Population Map Tools',
                            'Summary Information Buffer Map Tools',
                            'Summary Information Water Bodies Map Tools',
                            'Summary Information Wards Map Tools',
                            'Summary Information Road Map Tools',
                            'Summary Information Point Map Tools',
                            'Owner Information Map Tools',
                            'Solid Waste Payment Status Map Tools',
                            'Info Map Tools',
                        ]));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])
                        ->whereIn('name', [
                            'Distribution of SWM Services by Ward Chart',
                            'Outstanding Payments for SWM Services Chart'
                        ]));

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
