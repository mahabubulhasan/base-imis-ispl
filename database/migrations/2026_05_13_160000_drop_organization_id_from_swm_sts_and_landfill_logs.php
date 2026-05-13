<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('swm.sts_logs', 'organization_id')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id', 'operation_date']);
                $table->dropColumn('organization_id');
            });
        }

        if (Schema::hasColumn('swm.landfill_logs', 'organization_id')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropIndex(['organization_id', 'operation_date']);
                $table->dropColumn('organization_id');
            });
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
};
