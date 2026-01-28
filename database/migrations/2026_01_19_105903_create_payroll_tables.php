<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Salary Components (Allowances/Deductions)
        Schema::create('salary_components', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->enum('type', ['allowance', 'deduction']);
            $blueprint->enum('amount_type', ['fixed', 'percent']);
            $blueprint->decimal('amount', 15, 2);
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });

        // Salary Profiles for Employees
        Schema::create('salary_profiles', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('employee_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('base_salary', 15, 2);
            $blueprint->enum('salary_type', ['monthly', 'daily', 'hourly'])->default('monthly');
            $blueprint->string('payment_method')->nullable(); // bank, cash, cheque
            $blueprint->string('bank_name')->nullable();
            $blueprint->string('account_name')->nullable();
            $blueprint->string('account_number')->nullable();
            $blueprint->string('iban')->nullable();
            $blueprint->timestamps();
        });

        // Mapping Components to Employees
        Schema::create('employee_salary_components', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('employee_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('salary_component_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('custom_amount', 15, 2)->nullable(); // Override component default
            $blueprint->timestamps();
        });

        // Loans & Advance Management
        Schema::create('loans', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('employee_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('amount', 15, 2);
            $blueprint->integer('installments')->default(1);
            $blueprint->decimal('monthly_installment', 15, 2);
            $blueprint->decimal('remaining_balance', 15, 2);
            $blueprint->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $blueprint->date('approved_at')->nullable();
            $blueprint->timestamps();
        });

        // Payroll Run (The monthly batch)
        Schema::create('payroll_runs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->integer('month');
            $blueprint->integer('year');
            $blueprint->foreignId('branch_id')->nullable()->constrained();
            $blueprint->enum('status', ['draft', 'approved', 'locked', 'paid'])->default('draft');
            $blueprint->foreignId('processed_by')->nullable()->constrained('users');
            $blueprint->timestamp('processed_at')->nullable();
            $blueprint->timestamps();
        });

        // Payroll Items (Detailed breakdown for each employee)
        Schema::create('payroll_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('payroll_run_id')->constrained()->onDelete('cascade');
            $blueprint->foreignId('employee_id')->constrained()->onDelete('cascade');
            $blueprint->decimal('base_salary', 15, 2);
            $blueprint->decimal('total_allowances', 15, 2)->default(0);
            $blueprint->decimal('total_deductions', 15, 2)->default(0);
            $blueprint->decimal('overtime_pay', 15, 2)->default(0);
            $blueprint->decimal('attendance_deduction', 15, 2)->default(0);
            $blueprint->decimal('bonus', 15, 2)->default(0);
            $blueprint->decimal('loan_deduction', 15, 2)->default(0);
            $blueprint->decimal('net_salary', 15, 2);
            $blueprint->json('calculation_snapshot')->nullable(); // Store breakdown for payslip
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('employee_salary_components');
        Schema::dropIfExists('salary_profiles');
        Schema::dropIfExists('salary_components');
    }
};
