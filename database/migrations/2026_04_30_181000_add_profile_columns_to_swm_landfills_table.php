<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.landfills', function (Blueprint $table) {
            if (! Schema::hasColumn('swm.landfills', 'landfill_id')) {
                $table->string('landfill_id')->nullable()->after('id');
            }
            if (! Schema::hasColumn('swm.landfills', 'area')) {
                $table->decimal('area', 12, 2)->nullable()->after('capacity');
            }
            if (! Schema::hasColumn('swm.landfills', 'source_sts_ids')) {
                $table->json('source_sts_ids')->nullable()->after('area');
            }
            if (! Schema::hasColumn('swm.landfills', 'source_wards')) {
                $table->json('source_wards')->nullable()->after('source_sts_ids');
            }
            if (! Schema::hasColumn('swm.landfills', 'waste_type_ids')) {
                $table->json('waste_type_ids')->nullable()->after('reuse_practiced');
            }
            if (! Schema::hasColumn('swm.landfills', 'operational_status')) {
                $table->enum('operational_status', ['active', 'inactive'])->default('active')->after('treatment');
            }
        });

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS landfills_landfill_id_unique ON swm.landfills (landfill_id) WHERE landfill_id IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.landfills_landfill_id_unique');

        Schema::table('swm.landfills', function (Blueprint $table) {
            $columns = [
                'landfill_id',
                'area',
                'source_sts_ids',
                'source_wards',
                'waste_type_ids',
                'operational_status',
            ];

            $existing = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn('swm.landfills', $column)) {
                    $existing[] = $column;
                }
            }

            if (! empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
