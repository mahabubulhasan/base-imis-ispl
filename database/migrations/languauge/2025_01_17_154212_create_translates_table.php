<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranslatesTable extends Migration
{
        /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE schema  IF NOT EXISTS language');
        Schema::create('language.translates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key');
            $table->string('name');
            $table->text('text')->nullable();
            $table->string('pages')->default('global');
            $table->string('group')->default('system');
            $table->string('platform')->default('any');
            $table->boolean('load')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translates');
    }
}
