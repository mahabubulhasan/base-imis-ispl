<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swm.bill_collection_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('primary_collection_site_id')->constrained('swm.primary_collection_sites')->restrictOnDelete();
            $table->string('holding_number');
            $table->string('customer_id');
            $table->decimal('amount', 12, 2);
            $table->date('payment_for_month');
            $table->timestamp('payment_time');
            $table->string('payment_method');
            $table->foreignId('received_by_user_id')->nullable()->constrained('auth.users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['primary_collection_site_id', 'payment_for_month']);
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swm.bill_collection_payments');
    }
};
