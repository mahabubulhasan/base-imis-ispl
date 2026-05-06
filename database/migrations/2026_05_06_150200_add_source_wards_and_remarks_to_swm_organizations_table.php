<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->json('service_wards')->nullable()->after('organization_category_other');
            $table->text('remarks')->nullable()->after('service_wards');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->dropColumn(['service_wards', 'remarks']);
        });
    }
};
