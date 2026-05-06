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
            $table->string('organization_category')->nullable()->after('contact_number');
            $table->string('organization_category_other')->nullable()->after('organization_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('swm.organizations', function (Blueprint $table) {
            $table->dropColumn(['organization_category', 'organization_category_other']);
        });
    }
};
