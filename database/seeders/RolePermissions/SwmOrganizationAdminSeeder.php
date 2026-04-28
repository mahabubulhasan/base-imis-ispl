<?php

namespace Database\Seeders\RolePermissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SwmOrganizationAdminSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            [
                'name' => 'SW Organization - Admin',
            ],
        ];
        foreach ($roles as $role) {
            $createdRole = Role::updateOrCreate($role);
            if ($createdRole->name === 'SW Organization - Admin') {
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', [
                    'SW Service Provider Organizations',
                ]));
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', [
                    'SW Dashboard and KPIs',
                ]));
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['SW Service Provider Workers']));
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['SW Service Provider Vehicles']));
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['SW Service Provider Work Types', 'SW Service Provider Vehicle Types'])
                    ->whereIn('type', ['List', 'View']));
                $createdRole->givePermissionTo(Permission::all()->whereIn('group', ['Users'])
                    ->whereIn('type', [
                        'List',
                        'View',
                        'Add',
                        'Edit',
                        'Delete',
                        'Activity',
                    ]));
            }
        }
    }
}
