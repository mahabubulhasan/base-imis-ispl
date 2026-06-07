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

        DB::table($table)
            ->where('name', 'Export Households to CSV')
            ->update(['name' => 'Export Households to Excel']);

        if (! DB::table($table)->where('name', 'Import Households From Excel')->exists()) {
            DB::table($table)->insert([
                'name' => 'Import Households From Excel',
                'guard_name' => 'web',
                'group' => 'Building Info Households',
                'type' => 'Import',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        $table = $this->permissionsTable();

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where('name', 'Export Households to Excel')
            ->update(['name' => 'Export Households to CSV']);

        DB::table($table)->where('name', 'Import Households From Excel')->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
