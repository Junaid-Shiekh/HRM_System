<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL/MariaDB, we can use DB::statement to modify the enum
        DB::statement("ALTER TABLE payroll_runs MODIFY COLUMN status ENUM('draft', 'pending_approval', 'approved', 'submitted', 'paid') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payroll_runs MODIFY COLUMN status ENUM('draft', 'approved', 'locked', 'paid') DEFAULT 'draft'");
    }
};
