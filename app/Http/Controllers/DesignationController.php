<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\Department;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Services\DesignationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DesignationController extends Controller
{
    private DesignationService $designationService;

    public function __construct(DesignationService $designationService)
    {
        $this->designationService = $designationService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'department_id', 'status', 'name', 'perPage']);
        $designations = $this->designationService->list($filters);
        
        // For the dropdown in modal
        $departments = Department::where('status', 'active')->select('id', 'name')->get();

        return Inertia::render('Designations/Index', [
            'designations' => $designations,
            'departments' => $departments,
            'filters' => $filters,
        ]);
    }

    public function store(StoreDesignationRequest $request)
    {
        $this->designationService->create($request->validated());
        return redirect()->back()->with('success', 'Designation created successfully.');
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $this->designationService->update($designation, $request->validated());
        return redirect()->back()->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        $this->designationService->delete($designation);
        return redirect()->back()->with('success', 'Designation deleted successfully.');
    }
}
