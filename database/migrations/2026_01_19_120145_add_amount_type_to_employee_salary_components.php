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
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->enum('amount_type', ['fixed', 'percent'])->nullable()->after('custom_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->dropColumn('amount_type');
        });
    }
};
