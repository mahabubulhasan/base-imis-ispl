<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STANDARD_OPERATIONAL_TYPES = ['day', 'night', 'mobile', 'other'];

    public function up(): void
    {
        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->string('operational_type_other')->nullable()->after('operational_type');
        });

        DB::table('swm.vehicles')
            ->whereNotNull('operational_type')
            ->whereNotIn('operational_type', self::STANDARD_OPERATIONAL_TYPES)
            ->update([
                'operational_type_other' => DB::raw('operational_type'),
                'operational_type' => 'other',
            ]);

        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->dropColumn('vehicle_registration_no');
        });
    }

    public function down(): void
    {
        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->string('vehicle_registration_no')->nullable()->after('operational_type');
        });

        DB::table('swm.vehicles')
            ->where('operational_type', 'other')
            ->whereNotNull('operational_type_other')
            ->update([
                'operational_type' => DB::raw('operational_type_other'),
            ]);

        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->dropColumn('operational_type_other');
        });
    }
};
