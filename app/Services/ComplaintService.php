<?php

namespace App\Services;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ComplaintService extends BaseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['perPage'] ?? 10;
        $query = Complaint::query()->with('employee');

        // If user is employee, only show their complaints
        if (Auth::user()->hasRole('employee')) {
            $employee = \App\Models\Employee::withoutGlobalScopes()->where('user_id', Auth::id())->first();
            if ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                // Return empty if no employee linked
                $query->whereRaw('1 = 0');
            }
        }

        return $query
            ->when($filters['search'] ?? null, function (Builder $query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Complaint
    {
        $employee = \App\Models\Employee::withoutGlobalScopes()->where('user_id', Auth::id())->first();
        if (!$employee) {
            throw new \Exception('Logged in user is not an employee.');
        }

        $data['employee_id'] = $employee->id;
        $data['status'] = 'pending';

        return Complaint::create($data);
    }

    public function update(Complaint $complaint, array $data): Complaint
    {
        $complaint->update($data);
        return $complaint;
    }

    public function delete(Complaint $complaint): bool
    {
        return $complaint->delete();
    }
}
