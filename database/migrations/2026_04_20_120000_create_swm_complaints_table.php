<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_id')->unique();
            $table->timestamp('date_time');
            $table->string('holding_number')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('name');
            $table->string('contact_number');
            $table->string('complaint_type');
            $table->text('complaint_details');
            $table->string('submitted_through');
            $table->string('complaint_status');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('complaint_id');
            $table->index('date_time');
            $table->index('holding_number');
            $table->index('customer_id');
            $table->index('complaint_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.complaints');
    }
};
