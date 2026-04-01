<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePendingApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fsm.pending_applications', function (Blueprint $table) {
            $table->id();

            // FSM application form fields
            $table->string('tax_id')->nullable();
            $table->string('customer_name');
            $table->string('customer_contact');
            $table->string('holding_owner_name')->nullable();
            $table->string('ward')->nullable();
            $table->string('road_code')->nullable();
            $table->text('address')->nullable();
            $table->date('proposed_emptying_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_approved')->default(false);

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
        Schema::dropIfExists('fsm.pending_applications');
    }
}
