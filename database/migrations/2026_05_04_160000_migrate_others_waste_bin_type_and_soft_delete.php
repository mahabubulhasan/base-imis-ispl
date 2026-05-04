<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const OTHERS_NAME = 'Others (specify)';

    public function up(): void
    {
        $othersId = DB::table('swm.waste_bin_types')
            ->where('name', self::OTHERS_NAME)
            ->whereNull('deleted_at')
            ->value('id');

        if (! $othersId) {
            return;
        }

        $now = Carbon::now();

        DB::transaction(function () use ($othersId, $now) {
            $fallbackId = DB::table('swm.waste_bin_types')
                ->whereNull('deleted_at')
                ->where('id', '!=', $othersId)
                ->orderBy('id')
                ->value('id');

            if (! $fallbackId) {
                $fallbackId = DB::table('swm.waste_bin_types')->insertGetId([
                    'name' => 'Unspecified',
                    'created_at' => $now,
                    'updated_at' => $now,
                    'deleted_at' => null,
                ]);
            }

            $bins = DB::table('swm.waste_bins')
                ->where('waste_bin_type_id', $othersId)
                ->whereNull('deleted_at')
                ->get(['id', 'type_other_detail']);

            foreach ($bins as $bin) {
                $detail = trim((string) ($bin->type_other_detail ?? ''));
                if ($detail !== '') {
                    $name = Str::limit($detail, 255, '');
                    $newTypeId = DB::table('swm.waste_bin_types')
                        ->whereNull('deleted_at')
                        ->where('id', '!=', $othersId)
                        ->where('name', $name)
                        ->value('id');

                    if (! $newTypeId) {
                        $newTypeId = DB::table('swm.waste_bin_types')->insertGetId([
                            'name' => $name,
                            'created_at' => $now,
                            'updated_at' => $now,
                            'deleted_at' => null,
                        ]);
                    }
                } else {
                    $newTypeId = $fallbackId;
                }

                DB::table('swm.waste_bins')
                    ->where('id', $bin->id)
                    ->update([
                        'waste_bin_type_id' => $newTypeId,
                        'type_other_detail' => null,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('swm.waste_bin_types')
                ->where('id', $othersId)
                ->update([
                    'deleted_at' => $now,
                    'updated_at' => $now,
                ]);
        });
    }

    public function down(): void
    {
        // Data migration is not safely reversible.
    }
};
