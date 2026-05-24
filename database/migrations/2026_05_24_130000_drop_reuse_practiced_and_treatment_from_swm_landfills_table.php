<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.landfills', function (Blueprint $table) {
            $table->dropColumn(['reuse_practiced', 'treatment']);
        });
    }

    public function down(): void
    {
        Schema::table('swm.landfills', function (Blueprint $table) {
            $table->boolean('reuse_practiced')->nullable()->after('segregation_practiced');
            $table->boolean('treatment')->nullable()->after('leachate_collection_system_available');
        });
    }
};
