<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE swm.bill_collection_payments ADD COLUMN IF NOT EXISTS transaction_month date NULL');
        DB::statement("UPDATE swm.bill_collection_payments SET transaction_month = (payment_for_month + INTERVAL '1 month')::date WHERE transaction_month IS NULL");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE swm.bill_collection_payments DROP COLUMN IF EXISTS transaction_month');
    }
};
