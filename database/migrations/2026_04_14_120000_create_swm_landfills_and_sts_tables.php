<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.landfills', function (Blueprint $table) {
            $table->id();
            $table->string('landfill_id')->nullable();
            $table->string('name');
            $table->string('location')->nullable();
            $table->string('operator_name');
            $table->string('contact_number');
            $table->string('capacity')->nullable();
            $table->decimal('area', 12, 2)->nullable();
            $table->json('source_sts_ids')->nullable();
            $table->json('source_wards')->nullable();
            $table->boolean('segregation_practiced')->default(false);
            $table->boolean('reuse_practiced')->default(false);
            $table->json('waste_type_ids')->nullable();
            $table->string('monthly_waste_for_composting')->nullable();
            $table->boolean('treatment')->default(false);
            $table->enum('operational_status', ['active', 'inactive'])->default('active');
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

        DB::statement('CREATE UNIQUE INDEX landfills_landfill_id_unique ON swm.landfills (landfill_id) WHERE landfill_id IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.landfills_landfill_id_unique');
        Schema::dropIfExists('swm.sts');
        Schema::dropIfExists('swm.landfills');
    }
};
