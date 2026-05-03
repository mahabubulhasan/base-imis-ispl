<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.sts', function (Blueprint $table) {
            $table->string('sts_id')->nullable()->after('id');
            $table->unsignedSmallInteger('ward_no')->nullable()->after('location');
            $table->string('road_id')->nullable()->after('ward_no');
            $table->string('road_name')->nullable()->after('road_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('road_name');
            $table->decimal('longitude', 11, 7)->nullable()->after('latitude');
            $table->decimal('area', 12, 2)->nullable()->after('capacity');
            $table->json('source_wards')->nullable()->after('area');
            $table->json('waste_type_ids')->nullable()->after('segregation_practiced');
            $table->enum('operational_status', ['active', 'inactive'])->default('active')->after('destination_landfill_id');
        });

        DB::statement('CREATE UNIQUE INDEX sts_sts_id_unique ON swm.sts (sts_id) WHERE sts_id IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.sts_sts_id_unique');

        Schema::table('swm.sts', function (Blueprint $table) {
            $table->dropColumn([
                'sts_id',
                'ward_no',
                'road_id',
                'road_name',
                'latitude',
                'longitude',
                'area',
                'source_wards',
                'waste_type_ids',
                'operational_status',
            ]);
        });
    }
};
