<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.lics', function (Blueprint $table) {
            $table->id();
            $table->string('lic_id')->unique();
            $table->string('representative_name');
            $table->string('contact_no');
            $table->unsignedInteger('number_of_hhs');
            $table->unsignedInteger('total_population');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.lics');
    }
};
