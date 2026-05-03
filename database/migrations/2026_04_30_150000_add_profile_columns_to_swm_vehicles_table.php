<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->string('vehicle_id_no')->nullable()->after('vehicle_type_id');
            $table->string('service_area')->nullable()->after('driver_worker_id');
            $table->string('fuel_type')->nullable()->after('service_area');
            $table->string('operational_type')->nullable()->after('fuel_type');
            $table->string('vehicle_registration_no')->nullable()->after('operational_type');
            $table->string('engine_no')->nullable()->after('vehicle_registration_no');
            $table->string('chassis_no')->nullable()->after('engine_no');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('chassis_no');
            $table->unsignedSmallInteger('last_maintenance_year')->nullable()->after('status');
            $table->text('remarks')->nullable()->after('last_maintenance_year');
        });

        DB::statement('DROP INDEX IF EXISTS swm.vehicles_organization_vehicle_number_unique');
        DB::statement('CREATE UNIQUE INDEX vehicles_organization_vehicle_number_unique ON swm.vehicles (organization_id, vehicle_number) WHERE vehicle_number IS NOT NULL AND deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX vehicles_organization_vehicle_id_no_unique ON swm.vehicles (organization_id, vehicle_id_no) WHERE vehicle_id_no IS NOT NULL AND deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX vehicles_organization_chassis_no_unique ON swm.vehicles (organization_id, chassis_no) WHERE chassis_no IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.vehicles_organization_chassis_no_unique');
        DB::statement('DROP INDEX IF EXISTS swm.vehicles_organization_vehicle_id_no_unique');
        DB::statement('DROP INDEX IF EXISTS swm.vehicles_organization_vehicle_number_unique');
        DB::statement('CREATE UNIQUE INDEX vehicles_organization_vehicle_number_unique ON swm.vehicles (organization_id, vehicle_number) WHERE deleted_at IS NULL');

        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_id_no',
                'service_area',
                'fuel_type',
                'operational_type',
                'vehicle_registration_no',
                'engine_no',
                'chassis_no',
                'status',
                'last_maintenance_year',
                'remarks',
            ]);
        });
    }
};
