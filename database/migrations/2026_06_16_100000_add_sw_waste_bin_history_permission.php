<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function permissionsTable(): string
    {
        return config('permission.table_names.permissions', 'auth.permissions');
    }

    public function up(): void
    {
        $table = $this->permissionsTable();

        if (! Schema::hasTable($table)) {
            return;
        }

        $permission = [
            'group' => 'SW Waste Bins',
            'type' => 'History',
            'name' => 'View SW Waste Bin History',
        ];

        if (DB::table($table)->where('name', $permission['name'])->exists()) {
            return;
        }

        DB::table($table)->insert([
            'name' => $permission['name'],
            'guard_name' => 'web',
            'group' => $permission['group'],
            'type' => $permission['type'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $table = $this->permissionsTable();

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)->where('name', 'View SW Waste Bin History')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
