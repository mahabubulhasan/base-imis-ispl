<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.complaints', function (Blueprint $table) {
            $table->string('ward_no')->nullable()->after('contact_number');
            $table->date('incident_date')->nullable()->after('date_time');
            $table->boolean('duplicate_complaint')->default(false)->after('complaint_details');
            $table->string('duplicate_reference')->nullable()->after('duplicate_complaint');
            $table->smallInteger('priority_level')->nullable()->after('duplicate_reference');
            $table->string('assigned_to')->nullable()->after('priority_level');
            $table->integer('resolution_time_days')->nullable()->after('complaint_status');
            $table->string('photo_attachment_path')->nullable()->after('resolution_time_days');
        });

        DB::statement(
            'ALTER TABLE swm.complaints ADD CONSTRAINT complaints_priority_level_check CHECK (priority_level BETWEEN 1 AND 5)'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE swm.complaints DROP CONSTRAINT IF EXISTS complaints_priority_level_check');

        Schema::table('swm.complaints', function (Blueprint $table) {
            $table->dropColumn([
                'ward_no',
                'incident_date',
                'duplicate_complaint',
                'duplicate_reference',
                'priority_level',
                'assigned_to',
                'resolution_time_days',
                'photo_attachment_path',
            ]);
        });
    }
};
