<?php

namespace App\Services;

use App\Models\Warning;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WarningService extends BaseService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = Warning::with('employee')->latest();

        // If employee, only show their warnings
        if ($user->hasRole('employee')) {
            $employee = Employee::where('user_id', $user->id)->first();
            if ($employee) {
                $query->where('employee_id', $employee->id);
            } else {
                // If user has employee role but no employee record, return empty
                $query->where('id', 0);
            }
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhereHas('employee', function ($sq) use ($filters) {
                      $sq->where('first_name', 'like', "%{$filters['search']}%")
                         ->orWhere('last_name', 'like', "%{$filters['search']}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['perPage'] ?? 10)->withQueryString();
    }

    public function create(array $data): Warning
    {
        return Warning::create([
            'employee_id' => $data['employee_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'warning_date' => \Carbon\Carbon::parse($data['warning_date'])->toDateString(),
            'status' => 'pending',
        ]);
    }

    public function update(Warning $warning, array $data): bool
    {
        if (isset($data['warning_date'])) {
            $data['warning_date'] = \Carbon\Carbon::parse($data['warning_date'])->toDateString();
        }
        return $warning->update($data);
    }

    public function delete(Warning $warning): bool
    {
        return $warning->delete();
    }

    public function markAsRead(Warning $warning): bool
    {
        return $warning->update(['status' => 'read']);
    }
}
