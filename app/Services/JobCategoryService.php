<?php

namespace App\Services;

use App\Models\JobCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobCategoryService extends BaseService
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return JobCategory::query()
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): JobCategory
    {
        return JobCategory::create($data);
    }

    public function update(JobCategory $jobCategory, array $data): JobCategory
    {
        $jobCategory->update($data);
        return $jobCategory;
    }

    public function delete(JobCategory $jobCategory): bool
    {
        return $jobCategory->delete();
    }
}
