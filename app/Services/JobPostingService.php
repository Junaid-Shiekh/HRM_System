<?php

namespace App\Services;

use App\Models\JobPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class JobPostingService extends BaseService
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return JobPosting::query()
            ->with(['category', 'branch'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['branch_id'] ?? null, function (Builder $query, $branch_id) {
                $query->where('branch_id', $branch_id);
            })
            ->when($filters['job_category_id'] ?? null, function (Builder $query, $category_id) {
                $query->where('job_category_id', $category_id);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): JobPosting
    {
        $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        return JobPosting::create($data);
    }

    public function update(JobPosting $jobPosting, array $data): JobPosting
    {
        if (isset($data['title']) && $data['title'] !== $jobPosting->title) {
             $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        }
        $jobPosting->update($data);
        return $jobPosting;
    }

    public function delete(JobPosting $jobPosting): bool
    {
        return $jobPosting->delete();
    }
}
