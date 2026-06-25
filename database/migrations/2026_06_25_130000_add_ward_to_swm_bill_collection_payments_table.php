<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE swm.bill_collection_payments ADD COLUMN IF NOT EXISTS ward integer NULL');
        DB::statement('UPDATE swm.bill_collection_payments p SET ward = h.ward FROM building_info.households h WHERE p.household_id = h.id AND p.ward IS NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE swm.bill_collection_payments DROP COLUMN IF EXISTS ward');
    }
};
