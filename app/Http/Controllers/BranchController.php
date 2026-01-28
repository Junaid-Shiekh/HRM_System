<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Services\BranchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BranchController extends Controller
{
    private BranchService $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'name', 'perPage']);
        $branches = $this->branchService->list($filters);

        return Inertia::render('Branches/Index', [
            'branches' => $branches,
            'filters' => $filters,
        ]);
    }

    public function store(StoreBranchRequest $request)
    {
        $this->branchService->create($request->validated());
        return redirect()->back()->with('success', 'Branch created successfully.');
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $this->branchService->update($branch, $request->validated());
        return redirect()->back()->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        $this->branchService->delete($branch);
        return redirect()->back()->with('success', 'Branch deleted successfully.');
    }
}
