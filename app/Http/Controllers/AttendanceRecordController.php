<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Attendance/Records/Index', [
            'records' => AttendanceRecord::with(['employee', 'shift'])->latest()->get(),
            'employees' => Employee::all(),
            'shifts' => Shift::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required',
            'note' => 'nullable|string',
        ]);

        // Simple hour calculation if both times are present
        $totalHours = 0;
        if ($validated['clock_in'] && $validated['clock_out']) {
            $start = new \DateTime($validated['clock_in']);
            $end = new \DateTime($validated['clock_out']);
            $interval = $start->diff($end);
            $totalHours = $interval->h + ($interval->i / 60);
        }

        AttendanceRecord::create(array_merge($validated, ['total_hours' => $totalHours]));

        return redirect()->back()->with('success', 'Attendance record created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceRecord $attendanceRecord)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceRecord $attendanceRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendanceRecord $attendanceRecord)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'date' => 'required|date',
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'status' => 'required',
            'note' => 'nullable|string',
        ]);

        // Simple hour calculation
        $totalHours = 0;
        if ($validated['clock_in'] && $validated['clock_out']) {
            $start = new \DateTime($validated['clock_in']);
            $end = new \DateTime($validated['clock_out']);
            $interval = $start->diff($end);
            $totalHours = $interval->h + ($interval->i / 60);
        }

        $attendanceRecord->update(array_merge($validated, ['total_hours' => $totalHours]));

        return redirect()->back()->with('success', 'Attendance record updated successfully');
    }

    public function clockIn(Request $request)
    {
        $userId = auth()->guard('employee')->id() ?? auth()->guard('web')->id();
        $employee = Employee::where('user_id', $userId)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $clockInTime = now();
        $today = $clockInTime->toDateString();
        $attendanceDate = $today;
        $status = 'present';

        if ($employee->shift) {
            $shiftStart = \Carbon\Carbon::parse($today . ' ' . $employee->shift->start_time);
            $shiftEnd = \Carbon\Carbon::parse($today . ' ' . $employee->shift->end_time);

            // Handle midnight crossing
            if ($shiftEnd->lt($shiftStart)) {
                // If clocking in early morning, it's probably for the shift that started yesterday
                if ($clockInTime->hour < $shiftEnd->hour || ($clockInTime->hour == $shiftEnd->hour && $clockInTime->minute < $shiftEnd->minute)) {
                    $shiftStart->subDay();
                    $attendanceDate = $shiftStart->toDateString();
                }
            }

            $gracePeriod = $employee->attendancePolicy->late_grace_period ?? $employee->shift->grace_period ?? 0;
            
            if ($clockInTime->gt($shiftStart->copy()->addMinutes($gracePeriod))) {
                $status = 'late';
            }
        }

        $existingRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $attendanceDate)
            ->first();

        if ($existingRecord) {
            return redirect()->back()->with('error', 'You have already clocked in for this shift (' . $attendanceDate . ').');
        }

        AttendanceRecord::create([
            'employee_id' => $employee->id,
            'shift_id' => $employee->shift_id,
            'date' => $attendanceDate,
            'clock_in' => $clockInTime->toTimeString(),
            'status' => $status,
        ]);

        $message = $status === 'late' ? 'Clocked in successfully (marked Late).' : 'Clocked in successfully.';
        return redirect()->back()->with('success', $message . ' Time: ' . $clockInTime->format('H:i'));
    }

    public function clockOut(Request $request)
    {
        $userId = auth()->guard('employee')->id() ?? auth()->guard('web')->id();
        $employee = Employee::where('user_id', $userId)->first();
        
        if (!$employee) {
            return redirect()->back()->with('error', 'Employee record not found.');
        }

        $clockOutTime = now();
        $today = $clockOutTime->toDateString();

        // Check for record today or "yesterday" if it was a night shift
        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->whereNull('clock_out')
            ->latest('date')
            ->first();

        if (!$record) {
            return redirect()->back()->with('error', 'No active clock-in record found.');
        }

        $clockInTime = \Carbon\Carbon::parse($record->date . ' ' . $record->clock_in);
        $totalHours = $clockInTime->diffInMinutes($clockOutTime) / 60;

        $record->update([
            'clock_out' => $clockOutTime->toTimeString(),
            'total_hours' => $totalHours,
        ]);

        return redirect()->back()->with('success', 'Clocked out successfully at ' . $clockOutTime->format('H:i'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceRecord $attendanceRecord)
    {
        $attendanceRecord->delete();
        return redirect()->back()->with('success', 'Attendance record deleted successfully');
    }
}
