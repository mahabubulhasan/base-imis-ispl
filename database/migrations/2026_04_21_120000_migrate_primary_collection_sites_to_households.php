<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $swmPrimaryCollectionSitesExists = $this->tableExists('swm', 'primary_collection_sites');
        $householdsExists = $this->tableExists('building_info', 'households');

        if ($swmPrimaryCollectionSitesExists && ! $householdsExists) {
            if ($this->columnExists('swm', 'primary_collection_sites', 'customer_id')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites RENAME COLUMN customer_id TO household_id');
            }
            if ($this->columnExists('swm', 'primary_collection_sites', 'customer_name')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites RENAME COLUMN customer_name TO household_owner_name');
            }

            if ($this->columnExists('swm', 'primary_collection_sites', 'bin')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites ALTER COLUMN bin DROP NOT NULL');
            }

            DB::statement('ALTER TABLE swm.primary_collection_sites SET SCHEMA building_info');
            DB::statement('ALTER TABLE building_info.primary_collection_sites RENAME TO households');
            $householdsExists = true;
        }

        if ($this->tableExists('swm', 'bill_collection_payments')) {
            if ($this->constraintExists('swm', 'swm_bill_collection_payments_primary_collection_site_id_foreign')) {
                DB::statement('ALTER TABLE swm.bill_collection_payments DROP CONSTRAINT swm_bill_collection_payments_primary_collection_site_id_foreign');
            }
            if ($this->columnExists('swm', 'bill_collection_payments', 'primary_collection_site_id')) {
                DB::statement('ALTER TABLE swm.bill_collection_payments RENAME COLUMN primary_collection_site_id TO household_id');
            }

            if (
                $householdsExists &&
                $this->columnExists('swm', 'bill_collection_payments', 'household_id') &&
                ! $this->constraintExists('swm', 'swm_bill_collection_payments_household_id_foreign')
            ) {
                Schema::table('swm.bill_collection_payments', function (Blueprint $table) {
                    $table->foreign('household_id')->references('id')->on('building_info.households')->restrictOnDelete();
                });
            }
        }

        if ($householdsExists && ! $this->tableExists('swm', 'waste_bins')) {
            Schema::create('swm.waste_bins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('household_id')->unique()->constrained('building_info.households')->cascadeOnDelete();
                $table->string('bin')->nullable();
                $table->unsignedInteger('number_of_waste_bins');
                $table->decimal('total_capacity_kg', 12, 2);
                $table->softDeletes();
                $table->timestamps();
                $table->index('bin');
            });
        }
    }

    public function down(): void
    {
        if ($this->tableExists('swm', 'waste_bins')) {
            Schema::drop('swm.waste_bins');
        }

        if ($this->tableExists('swm', 'bill_collection_payments')) {
            if ($this->constraintExists('swm', 'swm_bill_collection_payments_household_id_foreign')) {
                DB::statement('ALTER TABLE swm.bill_collection_payments DROP CONSTRAINT swm_bill_collection_payments_household_id_foreign');
            }
            if ($this->columnExists('swm', 'bill_collection_payments', 'household_id')) {
                DB::statement('ALTER TABLE swm.bill_collection_payments RENAME COLUMN household_id TO primary_collection_site_id');
            }

            if (
                $this->tableExists('swm', 'primary_collection_sites') &&
                $this->columnExists('swm', 'bill_collection_payments', 'primary_collection_site_id') &&
                ! $this->constraintExists('swm', 'swm_bill_collection_payments_primary_collection_site_id_foreign')
            ) {
                Schema::table('swm.bill_collection_payments', function (Blueprint $table) {
                    $table->foreign('primary_collection_site_id')->references('id')->on('swm.primary_collection_sites')->restrictOnDelete();
                });
            }
        }

        if ($this->tableExists('building_info', 'households')) {
            DB::statement('ALTER TABLE building_info.households RENAME TO primary_collection_sites');
            DB::statement('ALTER TABLE building_info.primary_collection_sites SET SCHEMA swm');

            if ($this->columnExists('swm', 'primary_collection_sites', 'household_id')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites RENAME COLUMN household_id TO customer_id');
            }
            if ($this->columnExists('swm', 'primary_collection_sites', 'household_owner_name')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites RENAME COLUMN household_owner_name TO customer_name');
            }

            if ($this->columnExists('swm', 'primary_collection_sites', 'bin')) {
                DB::statement('ALTER TABLE swm.primary_collection_sites ALTER COLUMN bin SET NOT NULL');
            }
        }
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

    private function constraintExists(string $schema, string $constraintName): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $schema)
            ->where('constraint_name', $constraintName)
            ->exists();
    }
};
