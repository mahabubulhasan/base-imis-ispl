<?php

namespace Database\Seeders\RolePermissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MunicipalityExecutiveSeeder extends Seeder
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
                'name' => 'Municipality - Executive',
            ],
        ];

        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name) {
                case 'Municipality - Executive':    
                    
                    //For Building Module (Building Surveys locked out)
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Structures'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'History','Import']));

                    //For Low Income Communities
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Low Income Communities'])->whereIn('type', ['List', "View", 'Export', 'View on map']));

                    //For FSM Module
                    // Fecal Sludge IMS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Containments', 'Service Providers', 'Employee Infos', 'Desludging Vehicles', 'Treatment Plants', 'Treatment Plant Efficiency Tests', 'Applications', 'Emptyings', 'Sludge Collections', 'Feedbacks', 'Help Desks', 'Treatment Plant Efficiency Standards'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'Import','History']));

                    //For Sewer Connection Module -- locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Sewer Connection',])->whereIn('type', ['List', 'Approve', 'View on map', 'Delete']));

                    //For PT/CT Module -- locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['PT/CT Toilets', 'PT Users Logs'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'Import', 'History']));

                    //For CWIS Module -- locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['KPI Target'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'Import']));

                    //For Utility Module -- Sewer Network / Water Supply Network locked out
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Roads', 'Drain'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'Import', 'History']));

                    //For Payment ISS module -- Property Tax Collection ISS locked out
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Sw Service Payment', 'Water Supply ISS'])
                    ->whereIn('type', ['List', 'Export']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['SW Dashboard and KPIs'])
                    ->whereIn('type', ['List']));
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['SW Landfill Logs', 'SW Waste Processing'])
                    ->whereIn('type', ['List', 'View']));

                    //For Public Health Module
                    // Public Health ISS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Water Samples', 'Hotspots', 'Yearly Waterborne Cases'])->whereNotIn('type', ['Add', 'Edit', 'Delete', 'Import', 'History']));

                    //For Settings
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Users', 'Roles'])->whereIn('type', ['List', 'View']));

                    // CWIS IMS module locked out
                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['NSD Setting'])
                    //     ->whereIn('type', ['List']));

                    // $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['NSD'])
                    //     ->whereIn('type', ['Show']));

                    //Main Dashboard
                    $createdRole->givePermissionTo(
                        Permission::all()->whereIn('group', ['Dashboard'])
                    );

                    //Dashboard For Building
                    $createdRole->givePermissionTo(
                        Permission::all()->whereIn('group', ['Building Dashboard'])
                    );

                    //Dashboard For FSM -- locked out
                    // $createdRole->givePermissionTo(
                    //     Permission::all()->whereIn('group', ['FSM Dashboard'])
                    // );

                    //Dashboard For KPI -- locked out
                    // $createdRole->givePermissionTo(
                    //     Permission::all()->whereIn('group', ['KPI Dashboard'])
                    // );

                    //Dashboard For Utility
                    $createdRole->givePermissionTo(
                        Permission::all()->whereIn('group', ['Utility Dashboard'])
                    );

                    //For Maps
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Maps'])
                    ->whereNotIn('name', [
                        'Add Roads Map Tools'
                    ]));
                    break;
            }
        }
    }
}
