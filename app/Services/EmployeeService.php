<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeService extends BaseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        return Employee::query()
            ->with(['branch', 'department', 'designation', 'shift', 'attendancePolicy'])
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['branch_id'] ?? null, function (Builder $query, $branchId) {
                $query->where('branch_id', $branchId);
            })
            ->when($filters['department_id'] ?? null, function (Builder $query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($filters['designation_id'] ?? null, function (Builder $query, $designationId) {
                $query->where('designation_id', $designationId);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Employee
    {
        $password = $data['password'] ?? null;
        unset($data['password']);

        if ($password) {
            $user = \App\Models\User::create([
                'name' => $data['first_name'] . ' ' . $data['last_name'],
                'email' => $data['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($password),
                'status' => 'active',
            ]);

            // Assign employee role
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee']);
            $user->assignRole($role);

            $data['user_id'] = $user->id;
        }

        return Employee::create($data);
    }

    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee;
    }

    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }
}
