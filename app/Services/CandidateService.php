<?php

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CandidateService extends BaseService
{
    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Candidate::query()
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->withCount('applications')
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Candidate
    {
        return Candidate::create($data);
    }

    public function update(Candidate $candidate, array $data): Candidate
    {
        $candidate->update($data);
        return $candidate;
    }

    public function delete(Candidate $candidate): bool
    {
        return $candidate->delete();
    }
}
