<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Branch;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\JobPosting;
use App\Models\Candidate;
use App\Models\LeaveType;
use App\Models\Meeting;
use App\Models\Loan;
use App\Models\SalaryAdvance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        
        // 1. KPI Data
        $totalEmployees = Employee::count();
        $employeesThisMonth = Employee::whereBetween('created_at', [$startOfMonth, $now])->count();
        
        $totalBranches = Branch::count();
        $totalDepartments = Department::count();
        
        $pendingLeaves = LeaveApplication::where('status', 'pending')->count();
        $pendingLoans = Loan::where('status', 'pending')->count();
        $pendingAdvances = SalaryAdvance::where('status', 'pending')->count();
        
        $onLeaveToday = LeaveApplication::where('status', 'approved')
            ->where('start_date', '<=', $now->toDateString())
            ->where('end_date', '>=', $now->toDateString())
            ->count();
            
        $activeJobs = JobPosting::where('status', 'published')->count();
        $jobsThisMonth = JobPosting::where('status', 'published')
            ->whereBetween('created_at', [$startOfMonth, $now])
            ->count();
            
        $totalCandidates = Candidate::count();
        $candidatesThisMonth = Candidate::whereBetween('created_at', [$startOfMonth, $now])->count();

        // 2. Chart Data: Department Distribution
        $deptDistribution = Department::withCount('employees')
            ->get()
            ->map(fn($dept) => [
                'name' => $dept->name,
                'count' => $dept->employees_count
            ]);

        // 3. Chart Data: Hiring Trend (Last 6 Months)
        $hiringTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $count = Employee::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            
            $hiringTrend[] = [
                'month' => $month->format('M'),
                'count' => $count
            ];
        }

        // 4. Chart Data: Leave Status Distribution
        $leaveStats = [
            ['label' => 'Approved', 'count' => LeaveApplication::where('status', 'approved')->count()],
            ['label' => 'Pending', 'count' => LeaveApplication::where('status', 'pending')->count()],
            ['label' => 'Rejected', 'count' => LeaveApplication::where('status', 'rejected')->count()],
        ];

        // 5. Chart Data: Candidate Status
        $candidateStatusData = \App\Models\JobApplication::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn($item) => [
                'status' => $item->status->label(),
                'count' => $item->count
            ]);

        // 6. Chart Data: Leave Types Distribution
        $leaveTypesDistribution = LeaveType::withCount('leaveApplications')
            ->get()
            ->map(fn($type) => [
                'name' => $type->name,
                'count' => $type->leave_applications_count
            ]);

        // 7. Recent Activities
        $recentLeaveApplications = LeaveApplication::with(['employee', 'leaveType'])
            ->latest()
            ->take(4)
            ->get();

        $recentCandidates = Candidate::latest()
            ->take(4)
            ->get();

        $recentMeetings = Meeting::with(['type', 'room'])
            ->latest()
            ->take(4)
            ->get();

        // 8. Employee Growth
        $employeeGrowth = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthName = Carbon::create(null, $i)->format('M');
            $count = Employee::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $i)
                ->count();
            $employeeGrowth[] = ['month' => $monthName, 'count' => $count];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalEmployees' => $totalEmployees,
                'employeesThisMonth' => $employeesThisMonth,
                'totalBranches' => $totalBranches,
                'totalDepartments' => $totalDepartments,
                'pendingLeaves' => $pendingLeaves,
                'pendingLoans' => $pendingLoans,
                'pendingAdvances' => $pendingAdvances,
                'onLeaveToday' => $onLeaveToday,
                'activeJobs' => $activeJobs,
                'jobsThisMonth' => $jobsThisMonth,
                'totalCandidates' => $totalCandidates,
                'candidatesThisMonth' => $candidatesThisMonth,
            ],
            'charts' => [
                'deptDistribution' => $deptDistribution,
                'hiringTrend' => $hiringTrend,
                'leaveStats' => $leaveStats,
                'candidateStatus' => $candidateStatusData,
                'leaveTypes' => $leaveTypesDistribution,
                'employeeGrowth' => $employeeGrowth,
            ],
            'recent' => [
                'leaveApplications' => $recentLeaveApplications,
                'candidates' => $recentCandidates,
                'meetings' => $recentMeetings
            ]
        ]);
    }

    public function employeeDashboard(): Response
    {
        $userId = auth()->guard('employee')->id() ?? auth()->guard('web')->id();
        $employee = Employee::with(['shift', 'attendancePolicy'])
            ->where('user_id', $userId)
            ->first();

        $meetings = $employee ? $employee->meetings()->with(['type', 'room'])->latest()->take(10)->get() : [];
        $complaints = $employee ? \App\Models\Complaint::with('employee')->where('employee_id', $employee->id)->latest()->take(10)->get() : [];
        $warnings = $employee ? \App\Models\Warning::where('employee_id', $employee->id)->latest()->take(10)->get() : [];
        $loans = $employee ? Loan::where('employee_id', $employee->id)->latest()->get() : [];
        $advances = $employee ? SalaryAdvance::where('employee_id', $employee->id)->latest()->get() : [];
        
        $warningCount = $employee ? \App\Models\Warning::where('employee_id', $employee->id)->count() : 0;

        $todayAttendance = $employee ? \App\Models\AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first() : null;

        return Inertia::render('EmployeeDashboard', [
            'meetings' => $meetings,
            'complaints' => $complaints,
            'warnings' => $warnings,
            'loans' => $loans,
            'advances' => $advances,
            'warningCount' => $warningCount,
            'employee' => $employee,
            'todayAttendance' => $todayAttendance,
        ]);
    }
}
