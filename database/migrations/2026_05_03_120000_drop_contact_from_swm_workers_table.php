<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE swm.workers DROP COLUMN IF EXISTS contact');
    }

    public function down(): void
    {
        Schema::table('swm.workers', function (Blueprint $table) {
            $table->string('contact')->nullable();
        });
    }
};
