<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.workers', function (Blueprint $table) {
            $table->json('service_wards')->nullable()->after('service_area');
        });

        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->json('service_wards')->nullable()->after('service_area');
        });

        $this->backfillServiceWards('swm.workers');
        $this->backfillServiceWards('swm.vehicles');
    }

    public function down(): void
    {
        Schema::table('swm.workers', function (Blueprint $table) {
            $table->dropColumn('service_wards');
        });

        Schema::table('swm.vehicles', function (Blueprint $table) {
            $table->dropColumn('service_wards');
        });
    }

    private function backfillServiceWards(string $table): void
    {
        $rows = DB::table($table)
            ->select('id', 'service_area')
            ->whereNull('service_wards')
            ->whereNotNull('service_area')
            ->whereRaw("trim(service_area) <> ''")
            ->get();

        foreach ($rows as $row) {
            $serviceArea = trim((string) $row->service_area);
            if (! preg_match('/^\d+$/', $serviceArea)) {
                continue;
            }

            $ward = (int) $serviceArea;
            $exists = DB::table('layer_info.wards')->where('ward', $ward)->exists();
            if (! $exists) {
                continue;
            }

            DB::table($table)
                ->where('id', $row->id)
                ->update(['service_wards' => json_encode([$ward])]);
        }
    }
};
