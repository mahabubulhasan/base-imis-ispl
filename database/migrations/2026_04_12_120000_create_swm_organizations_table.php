<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS swm');

        Schema::create('swm.organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('address');
            $table->string('contact_person_name');
            $table->string('contact_number');
            $table->boolean('status')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('auth.users', function (Blueprint $table) {
            $table->unsignedBigInteger('swm_organization_id')->nullable()->after('service_provider_id');
            $table->foreign('swm_organization_id')
                ->references('id')
                ->on('swm.organizations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth.users', function (Blueprint $table) {
            $table->dropForeign(['swm_organization_id']);
            $table->dropColumn('swm_organization_id');
        });

        Schema::dropIfExists('swm.organizations');
    }
};
