<?php

namespace App\Http\Controllers;

use App\Models\Warning;
use App\Models\Employee;
use App\Services\WarningService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    private WarningService $warningService;

    public function __construct(WarningService $warningService)
    {
        $this->warningService = $warningService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'perPage']);
        $warnings = $this->warningService->list($filters);
        
        $isEmployee = Auth::user()->hasRole('employee');
        $employees = [];
        
        if (!$isEmployee) {
            $employees = Employee::select('id', 'first_name', 'last_name')->get();
        }

        return Inertia::render('Warnings/Index', [
            'warnings' => $warnings,
            'filters' => $filters,
            'employees' => $employees,
            'isHR' => !$isEmployee,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'warning_date' => 'required|date',
        ]);

        $this->warningService->create($validated);

        return redirect()->back()->with('success', 'Warning issued successfully.');
    }

    public function update(Request $request, Warning $warning)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'warning_date' => 'required|date',
            'status' => 'required|in:pending,read',
        ]);

        $this->warningService->update($warning, $validated);

        return redirect()->back()->with('success', 'Warning updated successfully.');
    }

    public function destroy(Warning $warning)
    {
        $this->warningService->delete($warning);
        return redirect()->back()->with('success', 'Warning deleted successfully.');
    }

    public function markAsRead(Warning $warning)
    {
        $this->warningService->markAsRead($warning);
        return redirect()->back()->with('success', 'Warning marked as read.');
    }
}
