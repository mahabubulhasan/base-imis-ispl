<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NsdSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cwis.nsd_setting', function (Blueprint $table) {
            $table->id();
            $table->string('nsd_username', 256);
            $table->string('city', 256);
            $table->text('api_post_url');
            $table->text('api_login_url');
            $table->string('nsd_password', 256);
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cwis.nsd_setting');
    }
}
