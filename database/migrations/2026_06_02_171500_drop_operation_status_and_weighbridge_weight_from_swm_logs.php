<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            $this->dropColumnPgsql('sts_logs', 'operation_status');
            $this->dropColumnPgsql('landfill_logs', 'operation_status');
            $this->dropColumnPgsql('landfill_logs', 'weighbridge_weight_ton');

            return;
        }

        if (Schema::hasColumn('swm.sts_logs', 'operation_status')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->dropIndex(['operation_status']);
                $table->dropColumn('operation_status');
            });
        }

        if (Schema::hasColumn('swm.landfill_logs', 'operation_status')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->dropIndex(['operation_status']);
                $table->dropColumn('operation_status');
            });
        }

        if (Schema::hasColumn('swm.landfill_logs', 'weighbridge_weight_ton')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->dropColumn('weighbridge_weight_ton');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('swm.sts_logs', 'operation_status')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->string('operation_status')->default('pending')->after('operation_date');
                $table->index('operation_status');
            });
        }

        if (! Schema::hasColumn('swm.landfill_logs', 'operation_status')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->string('operation_status')->default('pending')->after('operation_date');
                $table->index('operation_status');
            });
        }

        if (! Schema::hasColumn('swm.landfill_logs', 'weighbridge_weight_ton')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->decimal('weighbridge_weight_ton', 12, 3)->nullable()->after('quantity_ton');
            });
        }
    }

    protected function dropColumnPgsql(string $table, string $column): void
    {
        $exists = DB::selectOne(
            'select 1 as x from information_schema.columns where table_schema = ? and table_name = ? and column_name = ? limit 1',
            ['swm', $table, $column]
        );

        if ($exists) {
            DB::statement('alter table swm.'.$table.' drop column '.$column.' cascade');
        }
    }
};
