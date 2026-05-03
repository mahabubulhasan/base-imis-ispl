<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.waste_bin_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        $now = Carbon::now();
        $types = [
            'Container with Sticker',
            'Container without Sticker',
            'Trolley',
            'Concrete',
            'Dumper Placer',
            'Others (specify)',
        ];
        foreach ($types as $name) {
            DB::table('swm.waste_bin_types')->insert([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($this->tableExists('swm', 'waste_bins')) {
            $this->dropHouseholdIdUniqueIndexesOnWasteBinsIfExist();
            if ($this->columnExists('swm', 'waste_bins', 'number_of_waste_bins')) {
                Schema::table('swm.waste_bins', function (Blueprint $table) {
                    $table->dropColumn('number_of_waste_bins');
                });
            }
            DB::statement('CREATE INDEX IF NOT EXISTS waste_bins_household_id_index ON swm.waste_bins (household_id)');
        }

        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->foreignId('waste_bin_type_id')->nullable()->constrained('swm.waste_bin_types')->restrictOnDelete();
            $table->string('type_other_detail')->nullable();
            $table->boolean('placed_at_buildings')->default(true);
            $table->string('sub_location')->nullable();
            $table->unsignedSmallInteger('ward_no')->nullable();
            $table->string('road_no')->nullable();
            $table->string('road_name')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 11, 7)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('swm.waste_bins', function (Blueprint $table) {
            $table->dropForeign(['waste_bin_type_id']);
            $table->dropColumn([
                'waste_bin_type_id',
                'type_other_detail',
                'placed_at_buildings',
                'sub_location',
                'ward_no',
                'road_no',
                'road_name',
                'latitude',
                'longitude',
            ]);
        });

        DB::statement('DROP INDEX IF EXISTS swm.waste_bins_household_id_index');

        Schema::dropIfExists('swm.waste_bin_types');
    }

    private function dropHouseholdIdUniqueIndexesOnWasteBinsIfExist(): void
    {
        $rows = DB::select("
            SELECT tc.constraint_name
            FROM information_schema.table_constraints tc
            INNER JOIN information_schema.key_column_usage kcu
                ON tc.constraint_schema = kcu.constraint_schema
                AND tc.constraint_name = kcu.constraint_name
                AND tc.table_schema = kcu.table_schema
                AND tc.table_name = kcu.table_name
            WHERE tc.table_schema = 'swm'
                AND tc.table_name = 'waste_bins'
                AND tc.constraint_type = 'UNIQUE'
                AND kcu.column_name = 'household_id'
        ");
        foreach ($rows as $row) {
            $name = $row->constraint_name;
            DB::statement(
                'ALTER TABLE swm.waste_bins DROP CONSTRAINT IF EXISTS '.$this->quotePgIdent($name)
            );
        }
    }

    private function quotePgIdent(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    private function tableExists(string $schema, string $table): bool
    {
        return DB::table('information_schema.tables')
            ->where('table_schema', $schema)
            ->where('table_name', $table)
            ->exists();
    }

    private function columnExists(string $schema, string $table, string $column): bool
    {
        return DB::table('information_schema.columns')
            ->where('table_schema', $schema)
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->exists();
    }
};
