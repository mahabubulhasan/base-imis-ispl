<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.module_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('per_capita_sw_generation_kg_per_day', 6, 2)->default(0.52);
            $table->timestamps();
        });

        DB::table('swm.module_settings')->insert([
            'per_capita_sw_generation_kg_per_day' => 0.52,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.module_settings');
    }
};
