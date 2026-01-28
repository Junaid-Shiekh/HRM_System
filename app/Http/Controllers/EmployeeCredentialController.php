<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeCredentialController extends Controller
{
    public function index()
    {
        $employees = Employee::with('user.roles')->latest()->get();
        return Inertia::render('Employees/Credentials', [
            'employees' => $employees,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'password' => 'required|string|min:8',
        ]);

        $employee = Employee::findOrFail($request->employee_id);

        if ($employee->user_id) {
            $user = User::findOrFail($employee->user_id);
            $user->update([
                'password' => Hash::make($request->password),
                'user_type' => 'employee',
            ]);
            
            $guards = ['web', 'employee'];
            foreach ($guards as $guard) {
                $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => $guard]);
                if (!$user->hasRole($role, $guard)) {
                    $user->assignRole($role);
                }
            }
        } else {
            // Create user if not exists
            $user = User::create([
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $employee->email,
                'password' => Hash::make($request->password),
                'user_type' => 'employee',
                'status' => 'active',
            ]);

            $guards = ['web', 'employee'];
            foreach ($guards as $guard) {
                $role = Role::firstOrCreate(['name' => 'employee', 'guard_name' => $guard]);
                if (!$user->hasRole($role, $guard)) {
                    $user->assignRole($role);
                }
            }

            $employee->update(['user_id' => $user->id]);
        }

        return redirect()->back()->with('success', 'Credentials updated successfully.');
    }

    public function update(Request $request, $id)
    {
        // We use store for both create/update since it's a simple password set
        return $this->store($request);
    }
}
