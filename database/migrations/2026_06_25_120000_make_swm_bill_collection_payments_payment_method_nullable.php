<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE swm.bill_collection_payments ALTER COLUMN payment_method DROP NOT NULL');
    }

    public function down(): void
    {
        if (DB::table('swm.bill_collection_payments')->whereNull('payment_method')->exists()) {
            throw new \RuntimeException('Cannot reverse migration: swm.bill_collection_payments contains NULL payment_method.');
        }

        DB::statement('ALTER TABLE swm.bill_collection_payments ALTER COLUMN payment_method SET NOT NULL');
    }
};
