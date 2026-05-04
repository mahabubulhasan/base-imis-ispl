<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('building_info.households', function (Blueprint $table) {
            $table->string('father_or_husband_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('building_info.households', function (Blueprint $table) {
            $table->dropColumn('father_or_husband_name');
        });
    }
};
