<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
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
                'name' => 'Super Admin'
            ],
            [
                'name' => 'Municipality - Super Admin',
            ],
            [
                'name' => 'Municipality - Executive',
            ],
            [
                'name' => 'Municipality - Building Permit Department',
            ],
            [
                'name' => 'Municipality - Building Surveyor',
            ],
            [
                'name' => 'Municipality - Infrastructure Department',
            ],
            [
                'name' => 'Municipality - Tax Department',
            ],
            [
                'name' => 'Municipality - Water Billing Unit',
            ],
            [
                'name' => 'Municipality - Solid Waste Management Department',
            ],
            [
                'name' => 'Municipality - Sanitation Department',
            ],
            [
                'name' => 'Municipality - IT Admin',
            ],
            [
                'name' => 'Municipality - Public Health Department',
            ],
            [
                'name' => 'Municipality - Help Desk',
            ],
            [
                'name' => 'Service Provider - Admin',
            ],
            [
                'name' => 'Service Provider - Emptying Operator',
            ],
            [
                'name' => 'Service Provider - Help Desk',
            ],
            [
                'name' => 'Treatment Plant - Admin',
            ],
            [
                'name' => 'SWM Organization - Admin',
            ],
            [
                'name' => 'Guest',
            ],
        ];
        foreach ($roles as $role){
            Role::updateOrCreate($role);
        }

        // Assign Permissions to Each of the Role via Seeder
        $this->call(RolePermissions\MunicipalitySuperAdminSeeder::class);
        $this->call(RolePermissions\MunicipalityExecutiveSeeder::class);
        $this->call(RolePermissions\MunicipalityBuildingPermitDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalityBuildingSurveyorSeeder::class);
        $this->call(RolePermissions\MunicipalityInfrastructureDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalityTaxDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalityWaterBillingUnitSeeder::class);
        $this->call(RolePermissions\MunicipalitySolidWasteManagementDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalitySanitationDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalityITAdminSeeder::class);
        $this->call(RolePermissions\MunicipalityPublicHealthDepartmentSeeder::class);
        $this->call(RolePermissions\MunicipalityHelpDeskSeeder::class);
        $this->call(RolePermissions\ServiceProviderAdminSeeder::class);
        $this->call(RolePermissions\ServiceProviderEmptyingOperatorSeeder::class);
        $this->call(RolePermissions\ServiceProviderHelpDeskSeeder::class);
        $this->call(RolePermissions\TreatmentPlantAdminSeeder::class);
        $this->call(RolePermissions\SwmOrganizationAdminSeeder::class);
        $this->call(RolePermissions\GuestSeeder::class);
    }
}
