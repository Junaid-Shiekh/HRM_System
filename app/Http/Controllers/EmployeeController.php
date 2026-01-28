<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'branch_id', 'department_id', 'designation_id', 'perPage']);
        $employees = $this->employeeService->list($filters);

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'filters' => $filters,
            'branches' => Branch::where('status', 'active')->get(),
            'departments' => Department::where('status', 'active')->get(),
            'designations' => Designation::where('status', 'active')->get(),
            'shifts' => \App\Models\Shift::where('status', 'active')->get(),
            'attendancePolicies' => \App\Models\AttendancePolicy::where('status', 'active')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Employees/Create', [
            'branches' => Branch::where('status', 'active')->get(),
            'departments' => Department::where('status', 'active')->get(),
            'designations' => Designation::where('status', 'active')->get(),
            'shifts' => \App\Models\Shift::where('status', 'active')->get(),
            'attendancePolicies' => \App\Models\AttendancePolicy::where('status', 'active')->get(),
        ]);
    }

    public function store(StoreEmployeeRequest $request)
    {
        $this->employeeService->create($request->validated());
        return to_route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function edit(Employee $employee): Response
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $employee,
            'branches' => Branch::where('status', 'active')->get(),
            'departments' => Department::where('status', 'active')->get(),
            'designations' => Designation::where('status', 'active')->get(),
            'shifts' => \App\Models\Shift::where('status', 'active')->get(),
            'attendancePolicies' => \App\Models\AttendancePolicy::where('status', 'active')->get(),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->employeeService->update($employee, $request->validated());
        return to_route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $this->employeeService->delete($employee);
        return redirect()->back()->with('success', 'Employee deleted successfully.');
    }
}
