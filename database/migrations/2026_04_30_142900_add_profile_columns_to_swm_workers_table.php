<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swm.workers', function (Blueprint $table) {
            $table->string('worker_id_no')->nullable()->after('email');
            $table->unsignedSmallInteger('age')->nullable()->after('worker_id_no');
            $table->enum('gender', ['male', 'female', 'others'])->nullable()->after('age');
            $table->string('service_area')->nullable()->after('gender');
            $table->enum('employment_type', ['permanent', 'daily', 'contract'])->nullable()->after('service_area');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('employment_type');
            $table->string('department')->nullable()->after('status');
            $table->string('supervisor_name')->nullable()->after('department');
            $table->decimal('total_work_experience_years', 5, 2)->nullable()->after('supervisor_name');
            $table->decimal('organization_work_experience_years', 5, 2)->nullable()->after('total_work_experience_years');
            $table->enum('education_level', ['primary', 'secondary', 'below_ssc', 'ssc', 'hsc', 'bachelor', 'master', 'others'])->nullable()->after('organization_work_experience_years');
            $table->string('education_level_other')->nullable()->after('education_level');
            $table->string('employee_id')->nullable()->after('education_level_other');
            $table->string('national_id_no')->nullable()->after('employee_id');
        });

        DB::statement('CREATE UNIQUE INDEX workers_worker_id_no_unique ON swm.workers (worker_id_no) WHERE worker_id_no IS NOT NULL AND deleted_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX workers_employee_id_unique ON swm.workers (employee_id) WHERE employee_id IS NOT NULL AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS swm.workers_worker_id_no_unique');
        DB::statement('DROP INDEX IF EXISTS swm.workers_employee_id_unique');

        Schema::table('swm.workers', function (Blueprint $table) {
            $table->dropColumn([
                'worker_id_no',
                'age',
                'gender',
                'service_area',
                'employment_type',
                'status',
                'department',
                'supervisor_name',
                'total_work_experience_years',
                'organization_work_experience_years',
                'education_level',
                'education_level_other',
                'employee_id',
                'national_id_no',
            ]);
        });
    }
};
