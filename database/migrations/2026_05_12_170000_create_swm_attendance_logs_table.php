<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('swm.organizations')->restrictOnDelete();
            $table->foreignId('worker_id')->constrained('swm.workers')->restrictOnDelete();
            $table->string('department')->nullable();
            $table->foreignId('work_type_id')->nullable()->constrained('swm.work_types')->nullOnDelete();
            $table->string('work_type_name')->nullable();
            $table->string('supervisor_name')->nullable();
            $table->timestampTz('entry_at');
            $table->string('attendance_status');
            $table->timestampTz('check_in_at')->nullable();
            $table->timestampTz('check_out_at')->nullable();
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['organization_id', 'entry_at']);
            $table->index(['worker_id', 'entry_at']);
            $table->index('attendance_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.attendance_logs');
    }
};
