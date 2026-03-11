<?php

namespace App\Http\Controllers;

use App\Models\JobPosting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Website/Home');
    }

    public function about(): Response
    {
        return Inertia::render('Website/About');
    }

    public function jobs(): Response
    {
        $jobs = JobPosting::with(['category', 'branch'])
            ->where('status', 'published')
            ->latest()
            ->get();

        return Inertia::render('Website/Jobs', [
            'jobs' => $jobs
        ]);
    }

    public function submitApplication(Request $request)
    {
        $request->validate([
            'job_id' => 'required|exists:job_postings,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $job = JobPosting::findOrFail($request->job_id);

        // Find or create candidate linked to the same HR
        $candidate = \App\Models\Candidate::firstOrCreate(
            ['email' => $request->email, 'hr_id' => $job->hr_id],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
            ]
        );

        $application = new \App\Models\JobApplication();
        $application->hr_id = $job->hr_id;
        $application->job_posting_id = $job->id;
        $application->candidate_id = $candidate->id;
        $application->status = \App\Enums\ApplicationStatus::APPLIED;
        
        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $application->resume_path = $path;
        }

        $application->save();

        return redirect()->back()->with('success', 'Your application has been submitted successfully!');
    }
}

