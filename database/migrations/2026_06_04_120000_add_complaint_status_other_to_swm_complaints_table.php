<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.complaints', function (Blueprint $table) {
            $table->string('complaint_status_other')->nullable()->after('complaint_status');
        });
    }

    public function down(): void
    {
        Schema::table('swm.complaints', function (Blueprint $table) {
            $table->dropColumn('complaint_status_other');
        });
    }
};
