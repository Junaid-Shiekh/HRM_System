<?php

namespace App\Http\Controllers;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Enums\ApplicationStatus;
use App\Http\Requests\StoreJobApplicationRequest;
use App\Http\Requests\UpdateJobApplicationRequest;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JobApplicationController extends Controller
{
    private JobApplicationService $applicationService;

    public function __construct(JobApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'job_posting_id']);
        $perPage = $request->input('perPage', 10);
        $applications = $this->applicationService->list($filters, $perPage);

        return Inertia::render('Recruitment/Applications/Index', [
            'applications' => $applications,
            'filters' => $filters,
            'statuses' => collect(ApplicationStatus::cases())->map(fn($s) => ['value' => $s->value, 'label' => $s->label()]),
            'jobs' => JobPosting::select('id', 'title')->latest()->get(),
        ]);
    }
    
    // We might not need a create manual application often, but good to have
    public function store(StoreJobApplicationRequest $request)
    {
        $this->applicationService->create($request->validated());
        return redirect()->back()->with('success', 'Application recorded successfully.');
    }

    public function update(UpdateJobApplicationRequest $request, JobApplication $jobApplication)
    {
        try {
            $this->applicationService->updateStatus($jobApplication, $request->status);
            return redirect()->back()->with('success', 'Application status updated.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function destroy(JobApplication $jobApplication)
    {
        $this->applicationService->delete($jobApplication);
        return redirect()->back()->with('success', 'Application deleted.');
    }
}
