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
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
            'attachment' => 'nullable|string', // Should be file upload in practice
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        // Auto-set approved_by if approved
        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = auth()->id();
        }

        $this->leaveService->createApplication($validated);

        return to_route('leave-applications.index')->with('success', 'Leave application created successfully.');
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

        return to_route('leave-applications.index')->with('success', 'Leave application updated successfully.');
    }

    public function destroy(LeaveApplication $leaveApplication)
    {
        $this->leaveService->deleteApplication($leaveApplication);

        return back()->with('success', 'Leave application deleted successfully.');
    }
}
