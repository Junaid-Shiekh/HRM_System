<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRegularization;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRegularizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Attendance/Regularization/Index', [
            'requests' => AttendanceRegularization::with(['employee', 'attendanceRecord', 'approver'])->latest()->get(),
            'employees' => Employee::all(),
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
            'date' => 'required|date',
            'requested_clock_in' => 'required',
            'requested_clock_out' => 'required',
            'reason' => 'required|string',
        ]);

        // Find existing record if any
        $record = AttendanceRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->first();

        AttendanceRegularization::create(array_merge($validated, [
            'attendance_record_id' => $record?->id, // Allow null
            'original_clock_in' => $record?->clock_in,
            'original_clock_out' => $record?->clock_out,
            'status' => 'pending'
        ]));

        return redirect()->back()->with('success', 'Regularization request submitted');
    }

    public function update(Request $request, AttendanceRegularization $attendance_regularization)
    {
        if ($attendance_regularization->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be edited');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'requested_clock_in' => 'required',
            'requested_clock_out' => 'required',
            'reason' => 'required|string',
        ]);

        // Re-find record if employee or date changed
        $record = AttendanceRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->first();

        $attendance_regularization->update(array_merge($validated, [
            'attendance_record_id' => $record?->id,
            'original_clock_in' => $record?->clock_in,
            'original_clock_out' => $record?->clock_out,
        ]));

        return redirect()->back()->with('success', 'Regularization request updated');
    }

    public function approve(AttendanceRegularization $regularization)
    {
        $regularization->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

        // Update the actual attendance record
        $start = new \DateTime($regularization->requested_clock_in);
        $end = new \DateTime($regularization->requested_clock_out);
        $interval = $start->diff($end);
        $totalHours = $interval->h + ($interval->i / 60);

        if ($regularization->attendanceRecord) {
            $regularization->attendanceRecord->update([
                'clock_in' => $regularization->requested_clock_in,
                'clock_out' => $regularization->requested_clock_out,
                'total_hours' => $totalHours,
                'status' => 'present'
            ]);
        } else {
            // Create new record
            $attendanceRecord = AttendanceRecord::create([
                'employee_id' => $regularization->employee_id,
                'date' => $regularization->date,
                'clock_in' => $regularization->requested_clock_in,
                'clock_out' => $regularization->requested_clock_out,
                'total_hours' => $totalHours,
                'status' => 'present',
                'shift_id' => $regularization->employee->shift_id ?? null,
            ]);

            $regularization->update(['attendance_record_id' => $attendanceRecord->id]);
        }

        return redirect()->back()->with('success', 'Request approved and record updated');
    }

    public function reject(AttendanceRegularization $regularization)
    {
        $regularization->update([
            'status' => 'rejected',
            'approved_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Request rejected');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceRegularization $regularization)
    {
        $regularization->delete();
        return redirect()->back()->with('success', 'Request deleted');
    }
}
