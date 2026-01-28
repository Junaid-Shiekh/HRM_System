<?php

namespace App\Services;

use App\Models\Department;
use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentService extends BaseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return Department::query()
            ->with('branch')
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['branch_id'] ?? null, function (Builder $query, $branchId) {
                $query->where('branch_id', $branchId);
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

    public function create(array $data): Department
    {
        return Department::create($data);
    }

    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
