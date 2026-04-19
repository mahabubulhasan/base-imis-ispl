<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.primary_collection_sites', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('customer_name');
            $table->string('contact_number');
            $table->string('area_mohalla_name')->nullable();

            $table->string('bin');
            $table->unsignedInteger('ward')->nullable();
            $table->string('road_no_name')->nullable();
            $table->string('holding_number')->nullable();
            $table->string('tax_id')->nullable();

            $table->decimal('waste_charge', 12, 2)->nullable();
            $table->boolean('is_owner')->default(false);
            $table->string('functional_use')->nullable();
            $table->boolean('is_lic')->default(false);
            $table->string('lic_id')->nullable();
            $table->unsignedInteger('number_of_family_members')->nullable();
            $table->date('using_this_service_since')->nullable();
            $table->boolean('segregation_practiced')->default(false);
            $table->boolean('waste_bin_provided')->default(false);
            $table->decimal('daily_waste_volume', 12, 2)->nullable();
            $table->text('remarks')->nullable();
            $table->date('survey_date')->nullable();
            $table->foreignId('van_puller_id')->nullable()->constrained('swm.workers')->nullOnDelete();
            $table->string('van_puller_name')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['bin']);
            $table->index(['van_puller_id']);
            $table->index(['survey_date']);
            $table->index(['is_lic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.primary_collection_sites');
    }
};
