<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsInFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET search_path TO public, fsm');

        Schema::table('feedbacks', function (Blueprint $table) {
            $table->string('customer_address', 255)->nullable();
            $table->integer('service_quality_price')->nullable();
            $table->integer('service_delivery_efficiency')->nullable();
            $table->integer('fsm_quality_level')->default(-1);
            $table->boolean('price_reasonable')->default(true);
            $table->string('advertising_media', 250)->nullable();
            $table->string('safety_measures', 250)->nullable();
            $table->text('payment_mechanism_comments')->nullable();
            $table->integer('apply_in_future')->nullable();
            $table->text('apply_in_future_comments')->nullable();
            $table->integer('recommend_service')->nullable();
            $table->text('recommend_service_comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET search_path TO public, fsm');

        Schema::table('feedbacks', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_address',
                'service_quality_price',
                'service_delivery_efficiency',
                'fsm_quality_level',
                'price_reasonable',
                'advertising_media',
                'safety_measures',
                'payment_mechanism_comments',
                'apply_in_future',
                'apply_in_future_comments',
                'recommend_service',
                'recommend_service_comments',
            ]);
        });
    }
}
