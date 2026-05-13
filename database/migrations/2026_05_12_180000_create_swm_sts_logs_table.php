<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.sts_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('swm.organizations')->restrictOnDelete();
            $table->foreignId('vehicle_id')->constrained('swm.vehicles')->restrictOnDelete();
            $table->foreignId('vehicle_type_id')->nullable()->constrained('swm.vehicle_types')->nullOnDelete();
            $table->string('vehicle_type_name')->nullable();
            $table->foreignId('driver_worker_id')->nullable()->constrained('swm.workers')->nullOnDelete();
            $table->string('driver_name')->nullable();
            $table->foreignId('sts_id')->nullable()->constrained('swm.sts')->nullOnDelete();
            $table->string('sts_name')->nullable();
            $table->foreignId('waste_type_id')->nullable()->constrained('swm.waste_types')->nullOnDelete();
            $table->string('waste_type_name')->nullable();
            $table->decimal('quantity_ton', 12, 3)->nullable();
            $table->json('source_wards')->nullable();
            $table->timestampTz('entry_at');
            $table->date('operation_date');
            $table->string('operation_status');
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'operation_date']);
            $table->index(['vehicle_id', 'operation_date']);
            $table->index(['sts_id', 'operation_date']);
            $table->index('operation_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.sts_logs');
    }
};
