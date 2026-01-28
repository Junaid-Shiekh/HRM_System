<?php

namespace App\Http\Controllers;

use App\Models\JobCategory;
use App\Http\Requests\StoreJobCategoryRequest;
use App\Http\Requests\UpdateJobCategoryRequest;
use App\Services\JobCategoryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobCategoryController extends Controller
{
    private JobCategoryService $jobCategoryService;

    public function __construct(JobCategoryService $jobCategoryService)
    {
        $this->jobCategoryService = $jobCategoryService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);
        $categories = $this->jobCategoryService->list($filters);

        return Inertia::render('Recruitment/JobCategories/Index', [
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function store(StoreJobCategoryRequest $request)
    {
        $this->jobCategoryService->create($request->validated());
        return redirect()->back()->with('success', 'Job Category created successfully.');
    }

    public function update(UpdateJobCategoryRequest $request, JobCategory $jobCategory)
    {
        $this->jobCategoryService->update($jobCategory, $request->validated());
        return redirect()->back()->with('success', 'Job Category updated successfully.');
    }

    public function destroy(JobCategory $jobCategory)
    {
        $this->jobCategoryService->delete($jobCategory);
        return redirect()->back()->with('success', 'Job Category deleted successfully.');
    }
}
