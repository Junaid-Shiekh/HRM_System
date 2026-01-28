<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\WebsiteController::class, 'home'])->name('website.home');
Route::get('/about', [\App\Http\Controllers\WebsiteController::class, 'about'])->name('website.about');
Route::get('/jobs', [\App\Http\Controllers\WebsiteController::class, 'jobs'])->name('website.jobs');
Route::post('/jobs/apply', [\App\Http\Controllers\WebsiteController::class, 'submitApplication'])->name('website.jobs.apply');



Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('employee')->middleware(['auth:employee', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'employeeDashboard'])
        ->name('employee.dashboard');
    Route::post('/clock-in', [\App\Http\Controllers\AttendanceRecordController::class, 'clockIn'])->name('employee.clock-in');
    Route::post('/clock-out', [\App\Http\Controllers\AttendanceRecordController::class, 'clockOut'])->name('employee.clock-out');
});

Route::middleware('auth:web,employee')->group(function () {
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('branches', \App\Http\Controllers\BranchController::class);
    Route::resource('departments', \App\Http\Controllers\DepartmentController::class);
    Route::resource('designations', \App\Http\Controllers\DesignationController::class);
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::resource('employee-credentials', \App\Http\Controllers\EmployeeCredentialController::class);

    Route::resource('job-categories', \App\Http\Controllers\JobCategoryController::class);
    Route::resource('job-postings', \App\Http\Controllers\JobPostingController::class);
    Route::resource('candidates', \App\Http\Controllers\CandidateController::class);
    Route::resource('job-applications', \App\Http\Controllers\JobApplicationController::class);
    Route::resource('contract-types', \App\Http\Controllers\ContractTypeController::class);
    Route::resource('contracts', \App\Http\Controllers\ContractController::class);
    Route::resource('documents', \App\Http\Controllers\EmployeeDocumentController::class);
    Route::resource('meeting-types', \App\Http\Controllers\MeetingTypeController::class);
    Route::resource('meeting-rooms', \App\Http\Controllers\MeetingRoomController::class);
    Route::resource('permissions', \App\Http\Controllers\PermissionController::class);
    Route::resource('meetings', \App\Http\Controllers\MeetingController::class);
    Route::resource('complaints', \App\Http\Controllers\ComplaintController::class);
    Route::resource('warnings', \App\Http\Controllers\WarningController::class);
    Route::patch('warnings/{warning}/mark-as-read', [\App\Http\Controllers\WarningController::class, 'markAsRead'])->name('warnings.mark-as-read');


    // Leave Management
    Route::resource('leave-types', \App\Http\Controllers\LeaveTypeController::class);
    Route::resource('leave-policies', \App\Http\Controllers\LeavePolicyController::class);
    Route::resource('leave-applications', \App\Http\Controllers\LeaveApplicationController::class);

    // Attendance Management
    Route::resource('attendance-shifts', \App\Http\Controllers\ShiftController::class);
    Route::resource('attendance-policies', \App\Http\Controllers\AttendancePolicyController::class);
    Route::resource('attendance-records', \App\Http\Controllers\AttendanceRecordController::class);
    Route::resource('attendance-regularizations', \App\Http\Controllers\AttendanceRegularizationController::class);
    Route::post('attendance-regularizations/{regularization}/approve', [\App\Http\Controllers\AttendanceRegularizationController::class, 'approve'])->name('attendance-regularizations.approve');
    Route::post('attendance-regularizations/{regularization}/reject', [\App\Http\Controllers\AttendanceRegularizationController::class, 'reject'])->name('attendance-regularizations.reject');

    // Payroll Management
    Route::resource('salary-components', \App\Http\Controllers\SalaryComponentController::class);
    Route::resource('salary-profiles', \App\Http\Controllers\SalaryProfileController::class)->parameters(['salary-profiles' => 'employee']);
    Route::resource('payrolls', \App\Http\Controllers\PayrollController::class);
    Route::post('payrolls/generate', [\App\Http\Controllers\PayrollController::class, 'generate'])->name('payrolls.generate');
    Route::post('payrolls/{payroll}/submit-approval', [\App\Http\Controllers\PayrollController::class, 'submitForApproval'])->name('payrolls.submit-approval');
    Route::post('payrolls/{payroll}/approve', [\App\Http\Controllers\PayrollController::class, 'approve'])->name('payrolls.approve');
    Route::post('payrolls/{payroll}/finalize', [\App\Http\Controllers\PayrollController::class, 'finalize'])->name('payrolls.finalize');
    Route::get('payrolls/{payroll}/items/{item}/download', [\App\Http\Controllers\PayrollController::class, 'downloadPayslip'])->name('payrolls.items.download');
    Route::post('payrolls/{payroll}/items/{item}/mark-paid', [\App\Http\Controllers\PayrollController::class, 'markAsPaid'])->name('payrolls.items.mark-paid');
    Route::post('payrolls/{payroll}/items/{item}/update', [\App\Http\Controllers\PayrollController::class, 'updateItem'])->name('payrolls.items.update');
    Route::resource('loans', \App\Http\Controllers\LoanController::class);
    Route::post('loans/{loan}/repay', [\App\Http\Controllers\LoanController::class, 'repay'])->name('loans.repay');
    Route::post('loans/{loan}/approve', [\App\Http\Controllers\LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/reject', [\App\Http\Controllers\LoanController::class, 'reject'])->name('loans.reject');

    Route::resource('salary-advances', \App\Http\Controllers\SalaryAdvanceController::class);
    Route::post('salary-advances/{salary_advance}/approve', [\App\Http\Controllers\SalaryAdvanceController::class, 'approve'])->name('salary-advances.approve');
    Route::post('salary-advances/{salary_advance}/reject', [\App\Http\Controllers\SalaryAdvanceController::class, 'reject'])->name('salary-advances.reject');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
