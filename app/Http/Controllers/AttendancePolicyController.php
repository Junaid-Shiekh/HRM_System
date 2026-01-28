<?php

namespace App\Http\Controllers;

use App\Models\AttendancePolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendancePolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Attendance/Policies/Index', [
            'policies' => AttendancePolicy::latest()->get()
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
            'name' => 'required|string|max:255',
            'late_grace_period' => 'required|integer',
            'early_leave_grace_period' => 'required|integer',
            'overtime_rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        AttendancePolicy::create($validated);

        return redirect()->back()->with('success', 'Policy created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendancePolicy $attendancePolicy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendancePolicy $attendancePolicy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AttendancePolicy $attendancePolicy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'late_grace_period' => 'required|integer',
            'early_leave_grace_period' => 'required|integer',
            'overtime_rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $attendancePolicy->update($validated);

        return redirect()->back()->with('success', 'Policy updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendancePolicy $attendancePolicy)
    {
        $attendancePolicy->delete();
        return redirect()->back()->with('success', 'Policy deleted successfully');
    }
}
