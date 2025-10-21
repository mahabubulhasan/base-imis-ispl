<?php

namespace Database\Seeders\RolePermissions;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
class MunicipalityInfrastructureDepartmentSeeder extends Seeder
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
                'name' => 'Municipality - Infrastructure Department',
            ],
        ];
        foreach ($roles as $role){
            $createdRole = Role::updateOrCreate($role);
            switch ($createdRole->name){
                case 'Municipality - Infrastructure Department':
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['WaterSupply Network','Sewers','Sewer Connection','Drain','Roads','Utility Dashboard'])->whereNotIn('type',['History']));

                    $createdRole->givePermissionTo(
                        Permission::all()
                            ->whereIn('group', ['PT/CT Toilets'])
                            ->whereIn('type',['View','List','Export','View on map']));
                    $createdRole->givePermissionTo(
                        Permission::all()
                            ->whereIn('group', ['Building Structures', 'Building Surveys', 'Low Income Communities', 'Containments'])
                            ->whereIn('type',['View','List']));
                            $createdRole->givePermissionTo(Permission::all()->whereIn('group',['API'])
                            ->where('name','Access Sewer Connection API'));
                        
                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Data Export'])->whereIn('type',['Export']));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group',['Maps'])
                    ->whereIn('name',['Roads Map Layer','Sewers Line Map Layer','Drains Map Layer','WaterSupply Network Map Layer','Places Map Layer', 'Buildings Map Layer', 'Containments Map Layer','Wards Map Layer', 'Summarized Grids Map Layer', 'Water Body Map Layer', 'Land Use Map Layer', 'View Road On Map', 'Info Map Tools','Add Roads Map Tools','Decision Map Tools'
                    ,'Sewer Potential Map Tool','Buildings to Sewer Map Tools','Buildings to Road Map Tools','Building Close to Water Bodies Map Tools','Summary Information Buffer Map Tools','Summary Information Water Bodies Map Tools','Summary Information Wards Map Tools','Summary Information Road Map Tools','Summary Information Point Map Tools',
                    'Export in Decision Map Tools','Export in Summary Information Map Tools','Area Population Map Tools','Hard to Reach Buildings Map Tools',
                     'Export Data Map Tools','Filter by Wards Map Tools','Data Export Map Tools','Building by Structure Map Tools','Low Income Community Map Layer','Sanitation System Map Layer','Treatment Plants Map Layer',
                    'Municipality Map Layer','PT/CT Toilets Map Layer','Sewer Potential Map Tools','Ward Boundary Map Layer','General Map Tools',  'Containments Emptied Info Map Tools', 'Toilet Isochrone Map Tools', 'Export Containment Report'
                    ]));

                    $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Dashboard'])
                    ->whereIn('name', [
                        'Distribution of Water Supply Services by Ward Chart',
                        'Building Connections to Sanitation System Types Chart',
                        'Utility CountBox'
                        ])
                    );

            $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Building Dashboard'])
            ->whereIn('name', [
                'Building Use Composition Chart',
                    'Ward-Wise Distribution of Buildings Chart',
                    'Sanitation CountBox',
                    'Building CountBox'
        ])
        );
        $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['FSM Dashboard'])
            ->whereIn('name', [
                'Containment Types Categorized by Land Use Chart',
                                'Containment Types Categorized by Building Usage Chart','Ward-Wise Distribution of Containment Types in Residential Buildings Chart','Ward-Wise Distribution of Containment Types Chart',
                                'Proportion of Different Containment Types Chart'

        ])
        );
                break;
                    }
                }
            }
}
