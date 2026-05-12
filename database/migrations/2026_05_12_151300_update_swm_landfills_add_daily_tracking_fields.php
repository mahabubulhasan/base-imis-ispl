<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.landfills', function (Blueprint $table) {
            if (! Schema::hasColumn('swm.landfills', 'landfill_type_id')) {
                $table->foreignId('landfill_type_id')
                    ->nullable()
                    ->constrained('swm.landfill_types')
                    ->restrictOnDelete();
            }
            if (! Schema::hasColumn('swm.landfills', 'weighbridge_facility_available')) {
                $table->boolean('weighbridge_facility_available')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'boundary_wall_available')) {
                $table->boolean('boundary_wall_available')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'lighting_arrangement_available')) {
                $table->boolean('lighting_arrangement_available')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'manpower_deployed')) {
                $table->unsignedInteger('manpower_deployed')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'adequate_covering_arrangement_available')) {
                $table->boolean('adequate_covering_arrangement_available')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'gas_control_system_available')) {
                $table->boolean('gas_control_system_available')->nullable();
            }
            if (! Schema::hasColumn('swm.landfills', 'leachate_collection_system_available')) {
                $table->boolean('leachate_collection_system_available')->nullable();
            }

            if (Schema::hasColumn('swm.landfills', 'monthly_waste_for_composting')) {
                $table->dropColumn('monthly_waste_for_composting');
            }
        });
    }

    public function down(): void
    {
        Schema::table('swm.landfills', function (Blueprint $table) {
            if (! Schema::hasColumn('swm.landfills', 'monthly_waste_for_composting')) {
                $table->string('monthly_waste_for_composting')->nullable();
            }

            if (Schema::hasColumn('swm.landfills', 'landfill_type_id')) {
                $table->dropConstrainedForeignId('landfill_type_id');
            }

            $columns = [
                'weighbridge_facility_available',
                'boundary_wall_available',
                'lighting_arrangement_available',
                'manpower_deployed',
                'adequate_covering_arrangement_available',
                'gas_control_system_available',
                'leachate_collection_system_available',
            ];

            $existing = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('swm.landfills', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
