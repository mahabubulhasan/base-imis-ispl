<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 50);
            $table->string('transaction_id', 50);
            $table->integer('application_id')->nullable();
            $table->timestamp('payment_timestamp');
            $table->string('applicant_name');
            $table->string('applicant_contact', 20);
            $table->string('holding_owner_name');
            $table->string('address');
            $table->string('tax_code', 30);
            $table->string('service_type', 100);
            $table->date('proposed_service_date')->nullable();
            $table->string('transaction_status', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
