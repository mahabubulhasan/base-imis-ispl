<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE swm.landfills ALTER COLUMN segregation_practiced DROP NOT NULL');
    }

    public function down(): void
    {
        if (DB::table('swm.landfills')->whereNull('segregation_practiced')->exists()) {
            throw new \RuntimeException('Cannot reverse migration: swm.landfills contains NULL segregation_practiced.');
        }

        DB::statement('ALTER TABLE swm.landfills ALTER COLUMN segregation_practiced SET NOT NULL');
    }
};
