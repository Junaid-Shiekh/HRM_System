<?php

namespace App\Http\Controllers;

use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeavePolicyController extends Controller
{
    private LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'leave_type_id', 'status', 'perPage']);
        $leavePolicies = $this->leaveService->listPolicies($filters);

        return Inertia::render('LeavePolicies/Index', [
            'leavePolicies' => $leavePolicies,
            'leaveTypes' => LeaveType::where('status', 'active')->get(),
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leave_type_id' => 'required|exists:leave_types,id',
            'accrual_type' => 'nullable|string',
            'accrual_rate' => 'required|numeric|min:0',
            'carry_forward_limit' => 'required|integer|min:0',
            'min_days' => 'required|integer|min:1',
            'max_days' => 'nullable|integer|min:1',
            'requires_approval' => 'required|boolean',
            'status' => 'required|string|in:active,inactive',
        ]);

        $this->leaveService->createPolicy($validated);

        return to_route('leave-policies.index')->with('success', 'Leave policy created successfully.');
    }

    public function update(Request $request, LeavePolicy $leavePolicy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'leave_type_id' => 'required|exists:leave_types,id',
            'accrual_type' => 'nullable|string',
            'accrual_rate' => 'required|numeric|min:0',
            'carry_forward_limit' => 'required|integer|min:0',
            'min_days' => 'required|integer|min:1',
            'max_days' => 'nullable|integer|min:1',
            'requires_approval' => 'required|boolean',
            'status' => 'required|string|in:active,inactive',
        ]);

        $this->leaveService->updatePolicy($leavePolicy, $validated);

        return to_route('leave-policies.index')->with('success', 'Leave policy updated successfully.');
    }

    public function destroy(LeavePolicy $leavePolicy)
    {
        $this->leaveService->deletePolicy($leavePolicy);

        return back()->with('success', 'Leave policy deleted successfully.');
    }
}
