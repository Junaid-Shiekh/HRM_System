<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $routes = Route::getRoutes();
        $permissions = [];

        foreach ($routes as $route) {
            $name = $route->getName();
            
            if ($name && ! $this->shouldExclude($name)) {
                $permissions[] = $name;
            }
        }

        // Unique permissions
        $permissions = array_unique($permissions);

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create HR (Admin) role
        $hrRole = Role::firstOrCreate(['name' => 'hr', 'guard_name' => 'web']);
        
        // Assign all 'web' permissions to HR
        $webPermissions = Permission::where('guard_name', 'web')->get();
        $hrRole->syncPermissions($webPermissions);

        // Define and Assign Employee Permissions for both guards
        $guards = ['web', 'employee'];
        
        $employeePermissions = [
            'dashboard',
            'employee.dashboard',
            'meetings.index',
            'meetings.show',
            'complaints.index',
            'complaints.store',
            'complaints.create',
            'complaints.show',
            'complaints.update',
            'leave-applications.index',
            'leave-applications.create',
            'leave-applications.store',
            'leave-applications.show',
            'attendance-records.index',
            'documents.index',
            'documents.show',
            'profile.edit',
            'profile.update',
            'warnings.index',
            'warnings.create',
            'warnings.store',
            'warnings.show',
            'warnings.edit',
            'warnings.update',
            'warnings.destroy',
            'warnings.mark-as-read',
            // Payroll & Loans
            'payrolls.index',
            'payrolls.show',
            'payrolls.items.download',
            'loans.index',
            'loans.store',
            'loans.repay',
            'loans.approve',
            // Attendance Clock-in/out
            'employee.clock-in',
            'employee.clock-out',
            // Salary Advances
            'salary-advances.index',
            'salary-advances.store',
            'salary-advances.show',
            // Attendance Regularization
            'attendance-regularizations.index',
            'attendance-regularizations.create',
            'attendance-regularizations.store',
        ];

        foreach ($guards as $guard) {
            $employeeRole = Role::firstOrCreate(['name' => 'employee', 'guard_name' => $guard]);
            
            foreach ($employeePermissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }

            // Sync only permissions for THIS guard to THIS role
            $guardPermissions = Permission::where('guard_name', $guard)
                ->whereIn('name', $employeePermissions)
                ->get();
                
            $employeeRole->syncPermissions($guardPermissions);
        }

        $this->command->info('Permissions assigned to Employee role for both guards.');

        // Create or Update Super Admin User
        // Create or Update HR User
        $user = User::firstOrCreate(
            ['email' => 'hr@goritmi.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
            ]
        );

        $user->assignRole($hrRole);

        $this->command->info('Permissions seeded and assigned to HR Admin (hr@goritmi.com).');
    }

    /**
     * Determine if the route should be excluded from permissions.
     */
    private function shouldExclude(string $name): bool
    {
        $excludePrefixes = [
            'sanctum.',
            'ignition.',
            'verification.',
            'password.',
            'login',
            'logout',
            'register',
            'up',
            'telescope.',
            'horizon.',
            'profile.', // Usually standard Breeze/Jetstream profile routes
        ];

        foreach ($excludePrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
