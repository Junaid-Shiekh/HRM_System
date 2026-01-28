<?php

namespace App\Services;

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobApplicationService extends BaseService
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return JobApplication::query()
            ->with(['job', 'candidate'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->whereHas('candidate', function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['job_posting_id'] ?? null, function (Builder $query, $jobId) {
                $query->where('job_posting_id', $jobId);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): JobApplication
    {
        return JobApplication::create($data);
    }

    public function updateStatus(JobApplication $application, string $status): JobApplication
    {
        if ($application->status->value === 'rejected') {
            throw new \Exception('Cannot change the status of a rejected application.');
        }

        $application->update(['status' => $status]);

        
        // Load relationships if not loaded for the email
        $application->load(['candidate', 'job']);
        
        // Send email notification
        try {
            \Illuminate\Support\Facades\Mail::to($application->candidate->email)
                ->send(new \App\Mail\ApplicantStatusUpdate($application));
        } catch (\Exception $e) {
            // Log error but don't break the application flow
            \Illuminate\Support\Facades\Log::error("Failed to send status update email: " . $e->getMessage());
        }

        return $application;
    }


    public function delete(JobApplication $application): bool
    {
        return $application->delete();
    }
}
