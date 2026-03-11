<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\PayrollRun;
use App\Models\PayrollItem;
use App\Services\PayrollService;
use App\Mail\PayslipMail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PayrollController extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    public function index(Request $request)
    {
        $runs = PayrollRun::with(['branch', 'processor'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Payroll/Index', [
            'runs' => $runs,
            'branches' => Branch::all(),
            'months' => [
                ['id' => 1, 'name' => 'January'],
                ['id' => 2, 'name' => 'February'],
                ['id' => 3, 'name' => 'March'],
                ['id' => 4, 'name' => 'April'],
                ['id' => 5, 'name' => 'May'],
                ['id' => 6, 'name' => 'June'],
                ['id' => 7, 'name' => 'July'],
                ['id' => 8, 'name' => 'August'],
                ['id' => 9, 'name' => 'September'],
                ['id' => 10, 'name' => 'October'],
                ['id' => 11, 'name' => 'November'],
                ['id' => 12, 'name' => 'December'],
            ]
        ]);
    }

    public function show(PayrollRun $payroll)
    {
        // Automatically refresh numbers from latest setup if still in draft
        if ($payroll->status === 'draft') {
            $this->payrollService->generatePayroll($payroll->month, $payroll->year, $payroll->branch_id);
        }

        return Inertia::render('Payroll/Show', [
            'run' => $payroll->load(['items.employee.department', 'items.employee.salaryProfile', 'items.employee.loans', 'branch']),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        try {
            $run = $this->payrollService->generatePayroll(
                $validated['month'],
                $validated['year'],
                $validated['branch_id']
            );
            return redirect()->route('payrolls.show', $run->id)->with('success', 'Payroll generated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve(PayrollRun $payroll)
    {
        try {
            $this->payrollService->approvePayroll($payroll->id);
            return redirect()->back()->with('success', 'Payroll approved and locked for payment.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function downloadPayslip(PayrollRun $payroll, PayrollItem $item)
    {
        // Ensure item belongs to run
        if ($item->payroll_run_id !== $payroll->id) {
            abort(404);
        }

        $item->load(['employee.department', 'employee.designation', 'employee.salaryProfile']);
        
        $pdf = Pdf::loadView('payroll.payslip', [
            'run' => $payroll,
            'item' => $item
        ]);

        return $pdf->download("payslip-{$item->employee->employee_id}.pdf");
    }

    public function markAsPaid(Request $request, PayrollRun $payroll, PayrollItem $item)
    {
        if ($item->payroll_run_id !== $payroll->id) {
            abort(404);
        }

        $shouldDeduct = $request->input('deduct', true);

        if (!$shouldDeduct) {
            // Remove deductions from this item
            $item->loan_deduction = 0;
            $item->advance_deduction = 0;
        } else {
            // FORCE FULL PAYOFF: Calculate total remaining from all approved loans
            $totalLoanBalance = $item->employee->loans()->where('status', 'approved')->where('remaining_balance', '>', 0)->sum('remaining_balance');
            
            if ($totalLoanBalance > 0) {
                // Update the item to reflect the full payoff for historical accuracy and payslip
                $item->loan_deduction = $totalLoanBalance;
                
                // Update the snapshot so the PDF reflects the full payoff
                $snapshot = $item->calculation_snapshot;
                $snapshot['loans'] = [];
                foreach ($item->employee->loans as $loan) {
                    if ($loan->status === 'approved' && $loan->remaining_balance > 0) {
                        $snapshot['loans'][] = ['id' => $loan->id, 'amount' => $loan->remaining_balance];
                    }
                }
                $item->calculation_snapshot = $snapshot;
            }

            // Officially finalize the deduction from the loan/advance balance
            $this->payrollService->finalizeItemDeductions($item);
        }

        // Finalize Net Salary
        $item->net_salary = $item->base_salary + $item->total_allowances - $item->total_deductions - $item->loan_deduction - $item->advance_deduction - $item->attendance_deduction;

        $item->status = 'paid';
        $item->paid_at = now();
        $item->save();

        // Ensure we have fresh data with all relations for the PDF
        $item->refresh();
        $item->load(['employee', 'payrollRun', 'employee.salaryProfile', 'employee.department', 'employee.designation']);

        try {
            if ($item->employee && $item->employee->email) {
                // We pass the deduction status to the mail if needed, 
                // but the payslip PDF will automatically reflect the $item state.
                Mail::to($item->employee->email)->send(new PayslipMail($item));
            }

            // Check if all items in this run are paid
            $pendingItems = PayrollItem::where('payroll_run_id', $payroll->id)
                ->where('status', '!=', 'paid')
                ->count();
            
            if ($pendingItems === 0) {
                $payroll->update(['status' => 'paid']);
            }

            return redirect()->back()->with('success', "Salary marked as paid and emailed to {$item->employee->first_name}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('warning', "Salary marked as paid, but email failed: " . $e->getMessage());
        }
    }
}
