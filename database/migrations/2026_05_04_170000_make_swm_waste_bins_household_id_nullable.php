<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
        });

        DB::statement('ALTER TABLE swm.waste_bins ALTER COLUMN household_id DROP NOT NULL');

        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->foreign('household_id')
                ->references('id')
                ->on('building_info.households')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::table('swm.waste_bins')->whereNull('household_id')->exists()) {
            throw new \RuntimeException('Cannot reverse migration: swm.waste_bins contains NULL household_id.');
        }

        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->dropForeign(['household_id']);
        });

        DB::statement('ALTER TABLE swm.waste_bins ALTER COLUMN household_id SET NOT NULL');

        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->foreign('household_id')
                ->references('id')
                ->on('building_info.households')
                ->cascadeOnDelete();
        });
    }
};
