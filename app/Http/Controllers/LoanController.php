<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanController extends Controller
{
    public function index()
    {
        return Inertia::render('Payroll/Loans/Index', [
            'loans' => Loan::with('employee')->latest()->get(),
            'employees' => Employee::where('status', 'active')->get()
        ]);
    }

    public function store(Request $request)
    {
        $employeeId = null;
        if (auth()->guard('employee')->check()) {
            $employee = Employee::where('user_id', auth()->guard('employee')->id())->first();
            $employeeId = $employee->id;
        } else {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
            ]);
            $employeeId = $validated['employee_id'];
        }

        $validated = $request->merge(['employee_id' => $employeeId])->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:1',
            'installments' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $monthly = $validated['amount'] / $validated['installments'];

        Loan::create([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? '',
            'installments' => $validated['installments'],
            'monthly_installment' => $monthly,
            'remaining_balance' => $validated['amount'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Loan request submitted.');
    }

    public function approve(Loan $loan)
    {
        $loan->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        // Send Email notification
        \Illuminate\Support\Facades\Mail::to($loan->employee->email_personal ?? $loan->employee->email)
            ->send(new \App\Mail\LoanStatusChanged($loan));

        return redirect()->back()->with('success', 'Loan approved.');
    }

    public function reject(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $loan->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason']
        ]);

        // Send Email notification
        \Illuminate\Support\Facades\Mail::to($loan->employee->email_personal ?? $loan->employee->email)
            ->send(new \App\Mail\LoanStatusChanged($loan));

        return redirect()->back()->with('success', 'Loan rejected.');
    }

    public function repay(Request $request, Loan $loan)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $loan->remaining_balance,
            'repayment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($loan, $validated) {
            // Create repayment record
            \Illuminate\Support\Facades\DB::table('loan_repayments')->insert([
                'loan_id' => $loan->id,
                'amount' => $validated['amount'],
                'repayment_date' => $validated['repayment_date'],
                'notes' => $validated['notes'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update loan balance
            $loan->remaining_balance -= $validated['amount'];
            
            if ($loan->remaining_balance <= 0) {
                $loan->status = 'paid';
            }
            
            $loan->save();

            return redirect()->back()->with('success', 'Repayment processed successfully.');
        });
    }

    public function destroy(Loan $loan)
    {
        $loan->delete();
        return redirect()->back()->with('success', 'Loan deleted.');
    }
}
