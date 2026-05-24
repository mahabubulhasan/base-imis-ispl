<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('building_info.households', 'functional_use')) {
            return;
        }

        Schema::table('building_info.households', function (Blueprint $table) {
            $table->dropColumn('functional_use');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('building_info.households', 'functional_use')) {
            return;
        }

        Schema::table('building_info.households', function (Blueprint $table) {
            $table->string('functional_use')->nullable();
        });
    }
};
