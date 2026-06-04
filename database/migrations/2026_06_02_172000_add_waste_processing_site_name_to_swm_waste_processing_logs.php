<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('swm.waste_processing_logs', 'waste_processing_site_name')) {
            Schema::table('swm.waste_processing_logs', function (Blueprint $table) {
                $table->string('waste_processing_site_name')->nullable()->after('reporting_month');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('swm.waste_processing_logs', 'waste_processing_site_name')) {
            Schema::table('swm.waste_processing_logs', function (Blueprint $table) {
                $table->dropColumn('waste_processing_site_name');
            });
        }
    }
};
