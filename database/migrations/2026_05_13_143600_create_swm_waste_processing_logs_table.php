<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.waste_processing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('swm.organizations')->restrictOnDelete();
            $table->timestampTz('entry_at');
            $table->date('report_date');
            $table->date('reporting_month');
            $table->decimal('waste_received_ton', 12, 2)->nullable();
            $table->decimal('organic_waste_composted_ton', 12, 2)->nullable();
            $table->decimal('inorganic_waste_recycled_ton', 12, 2)->nullable();
            $table->decimal('waste_incinerated_ton', 12, 2)->nullable();
            $table->decimal('waste_burned_open_air_ton', 12, 2)->nullable();
            $table->decimal('residual_waste_landfilled_ton', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'report_date']);
            $table->index('reporting_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.waste_processing_logs');
    }
};
