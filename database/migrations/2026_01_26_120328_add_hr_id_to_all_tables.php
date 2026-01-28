<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'branches', 'departments', 'designations', 'employees', 'job_categories',
            'job_postings', 'candidates', 'job_applications', 'contract_types', 'contracts',
            'employee_documents', 'meetings', 'meeting_types', 'meeting_rooms', 'leave_types',
            'leave_policies', 'leave_applications', 'shifts', 'attendance_policies',
            'attendance_records', 'attendance_regularizations', 'complaints', 'warnings',
            'salary_components', 'salary_profiles', 'loans', 'payroll_runs', 'salary_advances'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'hr_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('hr_id')->nullable()->constrained('users')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'branches', 'departments', 'designations', 'employees', 'job_categories',
            'job_postings', 'candidates', 'job_applications', 'contract_types', 'contracts',
            'employee_documents', 'meetings', 'meeting_types', 'meeting_rooms', 'leave_types',
            'leave_policies', 'leave_applications', 'shifts', 'attendance_policies',
            'attendance_records', 'attendance_regularizations', 'complaints', 'warnings',
            'salary_components', 'salary_profiles', 'loans', 'payroll_runs', 'salary_advances'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('hr_id');
                });
            }
        }
    }
};
