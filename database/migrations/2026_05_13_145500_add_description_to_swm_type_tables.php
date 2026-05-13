<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.work_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('swm.vehicle_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('swm.waste_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });

        Schema::table('swm.landfill_types', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('swm.work_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('swm.vehicle_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('swm.waste_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('swm.landfill_types', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
