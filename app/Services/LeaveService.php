<?php

namespace App\Services;

use App\Models\LeaveType;
use App\Models\LeavePolicy;
use App\Models\LeaveApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveService extends BaseService
{
    // Leave Types
    public function listTypes(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return LeaveType::query()
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['is_paid'] ?? null, function (Builder $query, $isPaid) {
                $query->where('is_paid', $isPaid);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createType(array $data): LeaveType
    {
        return LeaveType::create($data);
    }

    public function updateType(LeaveType $leaveType, array $data): LeaveType
    {
        $leaveType->update($data);
        return $leaveType;
    }

    public function deleteType(LeaveType $leaveType): bool
    {
        return $leaveType->delete();
    }

    // Leave Policies
    public function listPolicies(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return LeavePolicy::query()
            ->with('leaveType')
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($filters['leave_type_id'] ?? null, function (Builder $query, $leaveTypeId) {
                $query->where('leave_type_id', $leaveTypeId);
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createPolicy(array $data): LeavePolicy
    {
        return LeavePolicy::create($data);
    }

    public function updatePolicy(LeavePolicy $leavePolicy, array $data): LeavePolicy
    {
        $leavePolicy->update($data);
        return $leavePolicy;
    }

    public function deletePolicy(LeavePolicy $leavePolicy): bool
    {
        return $leavePolicy->delete();
    }

    // Leave Applications
    public function listApplications(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return LeaveApplication::query()
            ->with(['employee', 'leaveType', 'approver'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['employee_id'] ?? null, function (Builder $query, $employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->when($filters['leave_type_id'] ?? null, function (Builder $query, $leaveTypeId) {
                $query->where('leave_type_id', $leaveTypeId);
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function createApplication(array $data): LeaveApplication
    {
        return LeaveApplication::create($data);
    }

    public function updateApplication(LeaveApplication $leaveApplication, array $data): LeaveApplication
    {
        $leaveApplication->update($data);
        return $leaveApplication;
    }

    public function deleteApplication(LeaveApplication $leaveApplication): bool
    {
        return $leaveApplication->delete();
    }
}
