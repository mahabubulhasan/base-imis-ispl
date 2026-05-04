<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('building_info.households', 'road_no')) {
            Schema::table('building_info.households', function (Blueprint $table) {
                $table->string('road_no')->nullable();
                $table->string('road_name')->nullable();
            });
        }

        if (! Schema::hasColumn('building_info.households', 'road_no_name')) {
            return;
        }

        DB::table('building_info.households')
            ->whereNotNull('road_no_name')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $v = trim((string) $row->road_no_name);
                    if ($v === '') {
                        continue;
                    }
                    $roadNo = null;
                    $roadName = $v;
                    if (str_contains($v, ' - ')) {
                        [$a, $b] = explode(' - ', $v, 2);
                        $a = trim($a);
                        $b = trim($b);
                        $roadNo = $a !== '' ? $a : null;
                        $roadName = $b !== '' ? $b : $a;
                    }
                    DB::table('building_info.households')->where('id', $row->id)->update([
                        'road_no' => $roadNo,
                        'road_name' => $roadName !== '' ? $roadName : null,
                    ]);
                }
            });

        Schema::table('building_info.households', function (Blueprint $table) {
            $table->dropColumn('road_no_name');
        });
    }

    public function down(): void
    {
        Schema::table('building_info.households', function (Blueprint $table) {
            $table->string('road_no_name')->nullable();
        });

        DB::table('building_info.households')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $parts = array_filter([trim((string) ($row->road_no ?? '')), trim((string) ($row->road_name ?? ''))]);
                    $joined = $parts === [] ? null : implode(' - ', $parts);
                    DB::table('building_info.households')->where('id', $row->id)->update([
                        'road_no_name' => $joined,
                    ]);
                }
            });

        Schema::table('building_info.households', function (Blueprint $table) {
            $table->dropColumn(['road_no', 'road_name']);
        });
    }
};
