<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.bill_collection_payments', function (Blueprint $table) {
            $table->string('receipt_copy_path')->nullable()->after('received_by_user_id');
            $table->index('receipt_copy_path');
        });
    }

    public function down(): void
    {
        Schema::table('swm.bill_collection_payments', function (Blueprint $table) {
            $table->dropIndex(['receipt_copy_path']);
            $table->dropColumn('receipt_copy_path');
        });
    }
};
