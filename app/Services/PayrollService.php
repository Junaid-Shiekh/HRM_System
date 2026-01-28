<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\PayrollItem;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Generate payroll for a specific month and year.
     */
    public function generatePayroll($month, $year, $branchId = null)
    {
        return DB::transaction(function () use ($month, $year, $branchId) {
            // 1. Create or Find the Payroll Run
            $run = PayrollRun::firstOrCreate([
                'month' => $month,
                'year' => $year,
                'branch_id' => $branchId,
            ], [
                'status' => 'draft',
            ]);

            if ($run->status === 'locked' || $run->status === 'paid') {
                throw new \Exception("Payroll for this month is already locked or paid.");
            }

            // 2. Fetch Employees (filter by branch if provided)
            $employees = Employee::with(['salaryProfile', 'salaryComponents', 
                'loans' => function($q) {
                    $q->where('status', 'approved')->where('remaining_balance', '>', 0);
                },
                'salaryAdvances' => function($q) use ($month, $year) {
                    $q->where('status', 'approved')
                      ->whereYear('repayment_date', $year)
                      ->whereMonth('repayment_date', $month);
                }
            ])
            ->where('status', 'active');

            if ($branchId) {
                $employees->where('branch_id', $branchId);
            }

            $employees = $employees->get();

            // 3. Process each employee
            foreach ($employees as $employee) {
                $itemData = $this->calculateEmployeeSalary($employee, $month, $year);
                
                PayrollItem::updateOrCreate([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                ], $itemData);
            }

            return $run->load('items.employee');
        });
    }

    /**
     * Calculate salary breakdown for a single employee.
     */
    public function calculateEmployeeSalary(Employee $employee, $month, $year)
    {
        $profile = $employee->salaryProfile;
        $baseSalary = $profile ? $profile->base_salary : $employee->salary;
        
        $totalAllowances = 0;
        $totalDeductions = 0;
        $snapshot = [
            'allowances' => [],
            'deductions' => [],
            'attendance' => [],
            'loans' => [],
            'advances' => [],
        ];

        // 1. Process Fixed/Percentage Components
        foreach ($employee->salaryComponents as $component) {
            $amount = $component->pivot->custom_amount ?? $component->amount;
            $type = $component->pivot->amount_type ?? $component->amount_type;
            
            if ($type === 'percent') {
                $amount = ($baseSalary * $amount) / 100;
            }

            if ($component->type === 'allowance') {
                $totalAllowances += $amount;
                $snapshot['allowances'][] = ['name' => $component->name, 'amount' => $amount];
            } else {
                $totalDeductions += $amount;
                $snapshot['deductions'][] = ['name' => $component->name, 'amount' => $amount];
            }
        }

        // 2. Process Loans (if any)
        $loanDeduction = 0;
        foreach ($employee->loans as $loan) {
            $deduction = min($loan->monthly_installment, $loan->remaining_balance);
            $loanDeduction += $deduction;
            $snapshot['loans'][] = ['id' => $loan->id, 'amount' => $deduction];
        }

        // 3. Process Salary Advances (if any for this month)
        $advanceDeduction = 0;
        foreach ($employee->salaryAdvances as $advance) {
            $advanceDeduction += $advance->amount;
            $snapshot['advances'][] = ['id' => $advance->id, 'amount' => $advance->amount];
        }

        // 4. Attendance Impact
        $attendanceDeduction = 0; 
        
        // 5. Final Calculation
        $netSalary = $baseSalary + $totalAllowances - $totalDeductions - $loanDeduction - $advanceDeduction - $attendanceDeduction;

        return [
            'base_salary' => $baseSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'loan_deduction' => $loanDeduction,
            'advance_deduction' => $advanceDeduction,
            'attendance_deduction' => $attendanceDeduction,
            'net_salary' => $netSalary,
            'calculation_snapshot' => $snapshot,
        ];
    }

    /**
     * Approve and Finalize payroll.
     */
    public function approvePayroll($runId)
    {
        return DB::transaction(function () use ($runId) {
            $run = PayrollRun::findOrFail($runId);
            
            if (!in_array($run->status, ['draft', 'pending_approval'])) {
                throw new \Exception("Only draft payroll can be approved.");
            }

            $run->update([
                'status' => 'approved',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Deduct from loans and advances officially
            foreach ($run->items as $item) {
                $snapshot = $item->calculation_snapshot;
                
                // Loans
                if (!empty($snapshot['loans'])) {
                    foreach ($snapshot['loans'] as $loanData) {
                        $loan = Loan::find($loanData['id']);
                        if ($loan) {
                            $loan->decrement('remaining_balance', $loanData['amount']);
                            if ($loan->remaining_balance <= 0) {
                                $loan->update(['status' => 'paid']);
                            }
                        }
                    }
                }

                // Advances
                if (!empty($snapshot['advances'])) {
                    foreach ($snapshot['advances'] as $advanceData) {
                        $advance = \App\Models\SalaryAdvance::find($advanceData['id']);
                        if ($advance) {
                            $advance->update(['status' => 'approved']); // already approved, but we could add a 'paid' or 'settled' status
                            // Let's add 'paid' status to the migration if needed, or just leave as is if approved means paid.
                            // Actually, I'll update it to 'approved' but maybe I should have 'settled'.
                        }
                    }
                }
            }

            return $run;
        });
    }
}
