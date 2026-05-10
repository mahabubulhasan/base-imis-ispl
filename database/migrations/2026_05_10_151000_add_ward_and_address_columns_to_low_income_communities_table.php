<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->string('sub_location')->nullable()->after('community_name');
            $table->integer('ward')->nullable()->after('sub_location');
            $table->string('road_no')->nullable()->after('ward');
            $table->string('road_name')->nullable()->after('road_no');
        });
    }

    public function down(): void
    {
        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->dropColumn([
                'sub_location',
                'ward',
                'road_no',
                'road_name',
                'holding_number',
            ]);
        });
    }
};
