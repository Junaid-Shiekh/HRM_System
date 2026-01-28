<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

class AssignEmployeeRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure employee role exists for both guards
        $guards = ['web', 'employee'];
        foreach ($guards as $guard) {
            Role::firstOrCreate(['name' => 'employee', 'guard_name' => $guard]);
        }

        // Find all employees
        $employees = Employee::all();

        $count = 0;
        foreach ($employees as $employee) {
            $user = null;
            if ($employee->user_id) {
                $user = User::find($employee->user_id);
            }
            
            if (!$user && $employee->email) {
                $user = User::where('email', $employee->email)->first();
                if ($user) {
                    $employee->user_id = $user->id;
                    $employee->save();
                }
            }

            if ($user) {
                foreach ($guards as $guard) {
                    if (!$user->roles()->where('name', 'employee')->where('guard_name', $guard)->exists()) {
                        $user->assignRole(Role::where('name', 'employee')->where('guard_name', $guard)->first());
                        $count++;
                    }
                }
            }
        }

        $this->command->info("Assigned 'employee' role (versions) to {$count} users.");
    }
}
