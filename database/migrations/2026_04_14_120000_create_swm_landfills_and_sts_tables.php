<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.landfills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('operator_name');
            $table->string('contact_number');
            $table->string('capacity')->nullable();
            $table->boolean('segregation_practiced')->default(false);
            $table->boolean('reuse_practiced')->default(false);
            $table->string('monthly_waste_for_composting')->nullable();
            $table->boolean('treatment')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('swm.sts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('operator_name');
            $table->string('contact_number');
            $table->string('capacity')->nullable();
            $table->boolean('segregation_practiced')->default(false);
            $table->foreignId('destination_landfill_id')->nullable()->constrained('swm.landfills')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.sts');
        Schema::dropIfExists('swm.landfills');
    }
};
