<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Employee;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveApplicationController extends Controller
{
    private LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'employee_id', 'leave_type_id', 'status', 'perPage']);
        $leaveApplications = $this->leaveService->listApplications($filters);

        return Inertia::render('LeaveApplications/Index', [
            'leaveApplications' => $leaveApplications,
            'leaveTypes' => LeaveType::where('status', 'active')->get(),
            'employees' => Employee::all(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isEmployee = $user->user_type === 'employee';

        $rules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|string',
        ];

        if (!$isEmployee) {
            $rules['employee_id'] = 'required|exists:employees,id';
            $rules['status'] = 'required|string|in:pending,approved,rejected';
        }

        $validated = $request->validate($rules);

        if ($isEmployee) {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
            $validated['employee_id'] = $employee->id;
            $validated['status'] = 'pending';
        }

        // Auto-set approved_by if approved
        if (isset($validated['status']) && $validated['status'] === 'approved') {
            $validated['approved_by'] = auth()->id();
        }

        $this->leaveService->createApplication($validated);

        return redirect()->back()->with('success', 'Leave application submitted successfully.');
    }

    public function update(Request $request, LeaveApplication $leaveApplication)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|string',
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        if ($validated['status'] === 'approved' && $leaveApplication->status !== 'approved') {
            $validated['approved_by'] = auth()->id();
        }

        $this->leaveService->updateApplication($leaveApplication, $validated);

        return redirect()->back()->with('success', 'Leave application updated successfully.');
    }

    public function destroy(LeaveApplication $leaveApplication)
    {
        $this->leaveService->deleteApplication($leaveApplication);

        return back()->with('success', 'Leave application deleted successfully.');
    }
}
