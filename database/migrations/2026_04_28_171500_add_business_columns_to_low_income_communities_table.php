<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->boolean('lic_status')->nullable()->after('community_name')->default(true);
            $table->decimal('area_decima', 12, 2)->nullable()->after('geom');
            $table->string('representative_name')->nullable()->after('area_decima');
            $table->string('representative_contact_no', 50)->nullable()->after('representative_name');
            $table->boolean('water_connection_status')->nullable()->after('population_others');
            $table->integer('no_of_wate_points')->nullable()->after('water_connection_status');
            $table->boolean('sanitation_status')->nullable()->after('no_of_wate_points');
            $table->text('remarks')->nullable()->after('no_of_community_toilets');
        });
    }

    public function down(): void
    {
        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->dropColumn([
                'lic_status',
                'area_decima',
                'representative_name',
                'representative_contact_no',
                'water_connection_status',
                'no_of_wate_points',
                'sanitation_status',
                'remarks',
            ]);
        });
    }
};
