<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Branch;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Services\DepartmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    private DepartmentService $departmentService;

    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'branch_id', 'status', 'name', 'perPage']);
        $departments = $this->departmentService->list($filters);
        
        // For the dropdown in modal
        $branches = Branch::where('status', 'active')->select('id', 'name')->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'branches' => $branches,
            'filters' => $filters,
        ]);
    }

    public function store(StoreDepartmentRequest $request)
    {
        $this->departmentService->create($request->validated());
        return redirect()->back()->with('success', 'Department created successfully.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->departmentService->update($department, $request->validated());
        return redirect()->back()->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $this->departmentService->delete($department);
        return redirect()->back()->with('success', 'Department deleted successfully.');
    }
}
