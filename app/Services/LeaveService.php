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

    public function getPolicyForEmployee($employee, $leaveTypeId): ?LeavePolicy
    {
        // 1. Check for designation-specific policy
        $policy = LeavePolicy::where('leave_type_id', $leaveTypeId)
            ->where('status', 'active')
            ->where('applicability_type', 'designation')
            ->whereJsonContains('applicability_ids', (string)$employee->designation_id)
            ->first();

        if ($policy) return $policy;

        // 2. Check for department-specific policy
        $policy = LeavePolicy::where('leave_type_id', $leaveTypeId)
            ->where('status', 'active')
            ->where('applicability_type', 'department')
            ->whereJsonContains('applicability_ids', (string)$employee->department_id)
            ->first();

        if ($policy) return $policy;

        // 3. Check for "all" policy
        return LeavePolicy::where('leave_type_id', $leaveTypeId)
            ->where('status', 'active')
            ->where('applicability_type', 'all')
            ->first();
    }

    public function getLeaveBalances($employee): array
    {
        $leaveTypes = LeaveType::where('status', 'active')->get();
        $balances = [];

        foreach ($leaveTypes as $type) {
            // Check gender applicability
            if ($type->gender !== 'all' && strtolower($type->gender) !== strtolower($employee->gender)) {
                continue;
            }

            $policy = $this->getPolicyForEmployee($employee, $type->id);
            
            // Entitlement should come from the policy's max_days (Yearly Entitlement)
            // or fallback to the leave type's default max_days.
            $entitlement = ($policy && $policy->max_days !== null) ? $policy->max_days : $type->max_days;
            
            $used = LeaveApplication::where('employee_id', $employee->id)
                ->where('leave_type_id', $type->id)
                ->where('status', 'approved')
                ->get()
                ->sum(function($app) {
                    $start = \Carbon\Carbon::parse($app->start_date);
                    $end = \Carbon\Carbon::parse($app->end_date);
                    return $start->diffInDays($end) + 1;
                });

            $balances[] = [
                'leave_type_id' => $type->id,
                'leave_type_name' => $type->name,
                'color' => $type->color,
                'entitlement' => $entitlement,
                'used' => $used,
                'remaining' => max(0, $entitlement - $used),
                'notice_period_days' => $policy ? $policy->notice_period_days : 0,
                'probation_restriction_days' => $policy ? $policy->probation_restriction_days : 0,
                'max_consecutive_days' => $policy ? $policy->max_consecutive_days : null,
                'max_days_per_request' => $policy ? $policy->max_days_per_request : null,
                'min_days' => $policy ? $policy->min_days : 1,
                'allow_half_day' => $policy ? $policy->allow_half_day : true,
            ];
        }

        return $balances;
    }
}
