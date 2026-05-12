<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->string('waste_bin_id')->nullable()->after('id');
        });

        $seq = 1;
        $rows = DB::table('swm.waste_bins')->orderBy('id')->pluck('id');

        foreach ($rows as $id) {
            $serial = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
            DB::table('swm.waste_bins')->where('id', $id)->update(['waste_bin_id' => 'WB'.$serial]);
            $seq++;
        }

        DB::statement('CREATE UNIQUE INDEX waste_bins_waste_bin_id_unique ON swm.waste_bins (waste_bin_id) WHERE waste_bin_id IS NOT NULL AND deleted_at IS NULL');

        DB::statement('ALTER TABLE swm.waste_bins ALTER COLUMN waste_bin_id SET NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.waste_bins_waste_bin_id_unique');

        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->dropColumn('waste_bin_id');
        });
    }
};
