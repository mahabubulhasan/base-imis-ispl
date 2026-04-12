<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('swm.vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('swm.organizations')->restrictOnDelete();
            $table->foreignId('vehicle_type_id')->constrained('swm.vehicle_types')->restrictOnDelete();
            $table->string('vehicle_number');
            $table->string('capacity')->nullable();
            $table->foreignId('driver_worker_id')->constrained('swm.workers')->restrictOnDelete();
            $table->string('dumping_place_kind');
            $table->foreignId('dumping_sts_id')->nullable()->constrained('swm.sts')->restrictOnDelete();
            $table->foreignId('dumping_landfill_id')->nullable()->constrained('swm.landfills')->restrictOnDelete();
            $table->text('dumping_place_other')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        DB::statement('CREATE UNIQUE INDEX vehicles_organization_vehicle_number_unique ON swm.vehicles (organization_id, vehicle_number) WHERE deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.vehicles_organization_vehicle_number_unique');

        Schema::dropIfExists('swm.vehicles');
        Schema::dropIfExists('swm.vehicle_types');
    }
};
