<?php

namespace App\Http\Controllers;

use App\Models\SalaryAdvance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SalaryAdvanceController extends Controller
{
    public function index()
    {
        // For HR/Admin view
        return Inertia::render('Payroll/SalaryAdvances/Index', [
            'advances' => SalaryAdvance::with('employee')->latest()->get(),
            'employees' => Employee::where('status', 'active')->get()
        ]);
    }

    public function store(Request $request)
    {
        $employeeId = null;
        
        // If it's an employee logged in, use their ID
        if (auth()->guard('employee')->check()) {
            $employee = Employee::where('user_id', auth()->guard('employee')->id())->first();
            $employeeId = $employee->id;
        } else {
            // HR/Admin creating for an employee
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
            ]);
            $employeeId = $validated['employee_id'];
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'nullable|string',
            'repayment_date' => 'nullable|date',
        ]);

        SalaryAdvance::create([
            'employee_id' => $employeeId,
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'repayment_date' => $validated['repayment_date'],
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Salary advance request submitted.');
    }

    public function approve(SalaryAdvance $advance)
    {
        $advance->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Send Email notification (to be implemented)
        \Illuminate\Support\Facades\Mail::to($advance->employee->email_personal ?? $advance->employee->email)
            ->send(new \App\Mail\SalaryAdvanceStatusChanged($advance));

        return redirect()->back()->with('success', 'Salary advance approved.');
    }

    public function reject(Request $request, SalaryAdvance $advance)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        $advance->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason']
        ]);

        // Send Email notification (to be implemented)
        \Illuminate\Support\Facades\Mail::to($advance->employee->email_personal ?? $advance->employee->email)
            ->send(new \App\Mail\SalaryAdvanceStatusChanged($advance));

        return redirect()->back()->with('success', 'Salary advance rejected.');
    }

    public function destroy(SalaryAdvance $advance)
    {
        $advance->delete();
        return redirect()->back()->with('success', 'Salary advance deleted.');
    }
}
