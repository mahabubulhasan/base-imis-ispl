<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'building_info.building_surveys';

    public function up(): void
    {
        Schema::table($this->table, function (Blueprint $table) {
            if (!Schema::hasColumn($this->table, 'ward')) $table->string('ward')->nullable();
            if (!Schema::hasColumn($this->table, 'road_code')) $table->string('road_code')->nullable();
            if (!Schema::hasColumn($this->table, 'house_number')) $table->string('house_number')->nullable();
            if (!Schema::hasColumn($this->table, 'functional_use_id')) $table->unsignedBigInteger('functional_use_id')->nullable();
            if (!Schema::hasColumn($this->table, 'use_category_id')) $table->unsignedBigInteger('use_category_id')->nullable();
            if (!Schema::hasColumn($this->table, 'water_source_id')) $table->unsignedBigInteger('water_source_id')->nullable();
            if (!Schema::hasColumn($this->table, 'sanitation_system_id')) $table->unsignedBigInteger('sanitation_system_id')->nullable();
            if (!Schema::hasColumn($this->table, 'sewer_code')) $table->string('sewer_code')->nullable();
            if (!Schema::hasColumn($this->table, 'drain_code')) $table->string('drain_code')->nullable();
            if (!Schema::hasColumn($this->table, 'payload_json')) $table->json('payload_json')->nullable();
        });

        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_tax_code_idx ON building_info.building_surveys (tax_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_ward_idx ON building_info.building_surveys (ward)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_road_code_idx ON building_info.building_surveys (road_code)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_house_number_idx ON building_info.building_surveys (house_number)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_functional_use_idx ON building_info.building_surveys (functional_use_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_use_category_idx ON building_info.building_surveys (use_category_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_sanitation_system_idx ON building_info.building_surveys (sanitation_system_id)');
        DB::statement('CREATE INDEX IF NOT EXISTS building_surveys_water_source_idx ON building_info.building_surveys (water_source_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_tax_code_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_ward_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_road_code_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_house_number_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_functional_use_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_use_category_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_sanitation_system_idx');
        DB::statement('DROP INDEX IF EXISTS building_info.building_surveys_water_source_idx');

        Schema::table($this->table, function (Blueprint $table) {
            foreach ([
                'ward', 'road_code', 'house_number', 'functional_use_id', 'use_category_id',
                'water_source_id', 'sanitation_system_id', 'sewer_code', 'drain_code', 'payload_json',
            ] as $column) {
                if (Schema::hasColumn($this->table, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

