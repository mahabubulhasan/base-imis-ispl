<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('layer_info.low_income_communities', 'lic_status')) {
            return;
        }

        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->dropColumn('lic_status');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('layer_info.low_income_communities', 'lic_status')) {
            return;
        }

        Schema::table('layer_info.low_income_communities', function (Blueprint $table) {
            $table->boolean('lic_status')->nullable()->after('community_name')->default(true);
        });
    }
};
