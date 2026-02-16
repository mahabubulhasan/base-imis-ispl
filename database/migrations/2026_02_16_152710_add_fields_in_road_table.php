<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInRoadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET search_path TO public, utility_info');
        Schema::table('roads', function (Blueprint $table) {
            $table->string('bn_name', 255)->nullable();
            $table->string('road_type', 100)->nullable();
            $table->integer('ward')->nullable();
            $table->string('road_uid', 30)->nullable();
            $table->string('road_ext', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET search_path TO public, utility_info');

        Schema::table('roads', function (Blueprint $table) {
            $table->dropColumn([
                'bn_name',
                'road_type',
                'ward',
                'road_uid',
                'road_ext',
            ]);
        });
    }
}
