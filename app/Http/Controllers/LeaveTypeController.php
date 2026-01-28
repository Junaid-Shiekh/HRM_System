<?php

namespace App\Http\Controllers;

use App\Models\LeaveType;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveTypeController extends Controller
{
    private LeaveService $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'is_paid', 'perPage']);
        $leaveTypes = $this->leaveService->listTypes($filters);

        return Inertia::render('LeaveTypes/Index', [
            'leaveTypes' => $leaveTypes,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_days' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'color' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $this->leaveService->createType($validated);

        return to_route('leave-types.index')->with('success', 'Leave type created successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'max_days' => 'required|integer|min:0',
            'is_paid' => 'required|boolean',
            'color' => 'nullable|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        $this->leaveService->updateType($leaveType, $validated);

        return to_route('leave-types.index')->with('success', 'Leave type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $this->leaveService->deleteType($leaveType);

        return back()->with('success', 'Leave type deleted successfully.');
    }
}
