<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use App\Models\JobCategory;
use App\Models\Branch;
use App\Enums\JobType;
use App\Http\Requests\StoreJobPostingRequest;
use App\Http\Requests\UpdateJobPostingRequest;
use App\Services\JobPostingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobPostingController extends Controller
{
    private JobPostingService $jobPostingService;

    public function __construct(JobPostingService $jobPostingService)
    {
        $this->jobPostingService = $jobPostingService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'branch_id', 'job_category_id']);
        $perPage = $request->input('perPage', 10);
        $postings = $this->jobPostingService->list($filters, $perPage);

        return Inertia::render('Recruitment/JobPostings/Index', [
            'postings' => $postings,
            'filters' => $filters,
            'categories' => JobCategory::where('status', 'active')->get(),
            'branches' => Branch::where('status', 'active')->get(),
            'jobTypes' => collect(JobType::cases())->map(fn($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Recruitment/JobPostings/Create', [
            'categories' => JobCategory::where('status', 'active')->get(),
            'branches' => Branch::where('status', 'active')->get(),
            'jobTypes' => collect(JobType::cases())->map(fn($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }

    public function store(StoreJobPostingRequest $request)
    {
        $this->jobPostingService->create($request->validated());
        return to_route('job-postings.index')->with('success', 'Job Posting created successfully.');
    }

    public function edit(JobPosting $jobPosting): Response
    {
        return Inertia::render('Recruitment/JobPostings/Edit', [
            'posting' => $jobPosting,
            'categories' => JobCategory::where('status', 'active')->get(),
            'branches' => Branch::where('status', 'active')->get(),
            'jobTypes' => collect(JobType::cases())->map(fn($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }

    public function update(UpdateJobPostingRequest $request, JobPosting $jobPosting)
    {
        $this->jobPostingService->update($jobPosting, $request->validated());
        return to_route('job-postings.index')->with('success', 'Job Posting updated successfully.');
    }

    public function destroy(JobPosting $jobPosting)
    {
        $this->jobPostingService->delete($jobPosting);
        return redirect()->back()->with('success', 'Job Posting deleted successfully.');
    }
}
