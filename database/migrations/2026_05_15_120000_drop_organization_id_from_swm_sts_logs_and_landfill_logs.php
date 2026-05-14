<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops organization_id from STS / landfill logs. Uses CASCADE so dependent
     * foreign keys and indexes are removed even if names differ from Laravel defaults.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            $this->dropOrganizationIdBlueprint('swm.sts_logs');
            $this->dropOrganizationIdBlueprint('swm.landfill_logs');

            return;
        }

        foreach (['sts_logs', 'landfill_logs'] as $table) {
            $exists = DB::selectOne(
                'select 1 as x from information_schema.columns where table_schema = ? and table_name = ? and column_name = ? limit 1',
                ['swm', $table, 'organization_id']
            );
            if ($exists) {
                DB::statement('alter table swm.'.$table.' drop column organization_id cascade');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('swm.sts_logs', 'organization_id')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->foreignId('organization_id')->after('id')->constrained('swm.organizations')->restrictOnDelete();
                $table->index(['organization_id', 'operation_date']);
            });
        }

        if (! Schema::hasColumn('swm.landfill_logs', 'organization_id')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->foreignId('organization_id')->after('id')->constrained('swm.organizations')->restrictOnDelete();
                $table->index(['organization_id', 'operation_date']);
            });
        }
    }

    protected function dropOrganizationIdBlueprint(string $table): void
    {
        if (! Schema::hasColumn($table, 'organization_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropForeign(['organization_id']);
            $blueprint->dropIndex(['organization_id', 'operation_date']);
            $blueprint->dropColumn('organization_id');
        });
    }
};
