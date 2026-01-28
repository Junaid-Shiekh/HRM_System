<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

use Illuminate\Support\Facades\Mail;
use App\Mail\MeetingInvitation;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Meeting::with(['employees', 'type', 'room'])->orderBy('start_time', 'desc');

        if (auth()->user()->hasRole('employee')) {
            $employee = Employee::where('user_id', auth()->id())->first();
            if ($employee) {
                $query->whereHas('employees', function($q) use ($employee) {
                    $q->where('employees.id', $employee->id);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $meetings = $query->get();

        return Inertia::render('Meetings/Index', [
            'meetings' => $meetings,
            'employees' => Employee::select('id', 'first_name', 'last_name', 'employee_id')->where('status', 'active')->get(),
            'meetingTypes' => \App\Models\MeetingType::where('status', 'active')->get(),
            'meetingRooms' => \App\Models\MeetingRoom::where('status', 'active')->get(),
            'isEmployee' => auth()->user()->hasRole('employee'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Meetings/Create', [
            'employees' => Employee::select('id', 'first_name', 'last_name', 'employee_id')->where('status', 'active')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:255',
            'status' => 'required|string',
            'employees' => 'required|array',
            'employees.*' => 'exists:employees,id',
            'meeting_type_id' => 'nullable|exists:meeting_types,id',
            'meeting_room_id' => 'nullable|exists:meeting_rooms,id',
        ]);

        $meeting = Meeting::create($request->except('employees'));
        $meeting->employees()->sync($request->employees);

        // Send emails to invited employees
        foreach ($meeting->employees as $employee) {
            if ($employee->email) {
                Mail::to($employee->email)->send(new MeetingInvitation($meeting));
            }
        }

        return redirect()->route('meetings.index')->with('success', 'Meeting scheduled and invitations sent successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Meeting $meeting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Meeting $meeting)
    {
        $meeting->load('employees');
        return Inertia::render('Meetings/Edit', [
            'meeting' => $meeting,
            'employees' => Employee::select('id', 'first_name', 'last_name', 'employee_id')->where('status', 'active')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'meeting_link' => 'nullable|url|max:255',
            'status' => 'required|string',
            'employees' => 'required|array',
            'employees.*' => 'exists:employees,id',
            'meeting_type_id' => 'nullable|exists:meeting_types,id',
            'meeting_room_id' => 'nullable|exists:meeting_rooms,id',
        ]);

        $meeting->update($request->except('employees'));
        $meeting->employees()->sync($request->employees);

        return redirect()->route('meetings.index')->with('success', 'Meeting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();
        return redirect()->route('meetings.index')->with('success', 'Meeting deleted successfully.');
    }
}
