<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops organization_id from waste processing logs. On PostgreSQL uses CASCADE
     * so dependent foreign keys and indexes are removed even if names differ from Laravel defaults.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            $exists = DB::selectOne(
                'select 1 as x from information_schema.columns where table_schema = ? and table_name = ? and column_name = ? limit 1',
                ['swm', 'waste_processing_logs', 'organization_id']
            );
            if ($exists) {
                DB::statement('alter table swm.waste_processing_logs drop column organization_id cascade');
            }

            return;
        }

        if (Schema::hasColumn('swm.waste_processing_logs', 'organization_id')) {
            Schema::table('swm.waste_processing_logs', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id', 'report_date']);
                $table->dropColumn('organization_id');
            });
        }
    }

    public function down(): void
    {
        $exists = Schema::getConnection()->getDriverName() === 'pgsql'
            ? DB::selectOne(
                'select 1 as x from information_schema.columns where table_schema = ? and table_name = ? and column_name = ? limit 1',
                ['swm', 'waste_processing_logs', 'organization_id']
            )
            : null;

        if (Schema::getConnection()->getDriverName() === 'pgsql' ? ! $exists : ! Schema::hasColumn('swm.waste_processing_logs', 'organization_id')) {
            Schema::table('swm.waste_processing_logs', function (Blueprint $table) {
                // Nullable so rollback works when rows already exist (NOT NULL + no default would fail).
                $table->foreignId('organization_id')->nullable()->after('id')->constrained('swm.organizations')->restrictOnDelete();
                $table->index(['organization_id', 'report_date']);
            });
        }
    }
};
