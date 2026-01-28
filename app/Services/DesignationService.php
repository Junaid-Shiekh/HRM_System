<?php

namespace App\Services;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DesignationService extends BaseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return Designation::query()
            ->with('department')
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['department_id'] ?? null, function (Builder $query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['name'] ?? null, function (Builder $query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Designation
    {
        return Designation::create($data);
    }

    public function update(Designation $designation, array $data): Designation
    {
        $designation->update($data);
        return $designation;
    }

    public function delete(Designation $designation): bool
    {
        return $designation->delete();
    }
}
