<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.module_settings', function (Blueprint $table) {
            $table->text('description')->nullable()->after('per_capita_sw_generation_kg_per_day');
        });
    }

    public function down(): void
    {
        Schema::table('swm.module_settings', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
