<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('swm.sts_logs', 'waste_type_ids')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->json('waste_type_ids')->nullable();
            });
        }

        if (! Schema::hasColumn('swm.landfill_logs', 'waste_type_ids')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->json('waste_type_ids')->nullable();
            });
        }

        DB::statement('UPDATE swm.sts_logs SET waste_type_ids = jsonb_build_array(waste_type_id) WHERE waste_type_id IS NOT NULL');
        DB::statement('UPDATE swm.landfill_logs SET waste_type_ids = jsonb_build_array(waste_type_id) WHERE waste_type_id IS NOT NULL');
    }

    public function down(): void
    {
        if (Schema::hasColumn('swm.sts_logs', 'waste_type_ids')) {
            Schema::table('swm.sts_logs', function (Blueprint $table) {
                $table->dropColumn('waste_type_ids');
            });
        }

        if (Schema::hasColumn('swm.landfill_logs', 'waste_type_ids')) {
            Schema::table('swm.landfill_logs', function (Blueprint $table) {
                $table->dropColumn('waste_type_ids');
            });
        }
    }
};
