<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Attendance/Shifts/Index', [
            'shifts' => Shift::latest()->get()
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
            'start_time' => 'required',
            'end_time' => 'required',
            'break_duration' => 'required|integer',
            'grace_period' => 'required|integer',
            'type' => 'required|in:day,night',
            'status' => 'required|in:active,inactive',
        ]);

        Shift::create($validated);

        return redirect()->back()->with('success', 'Shift created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Shift $shift)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Shift $shift)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'end_time' => 'required',
            'break_duration' => 'required|integer',
            'grace_period' => 'required|integer',
            'type' => 'required|in:day,night',
            'status' => 'required|in:active,inactive',
        ]);

        $shift->update($validated);

        return redirect()->back()->with('success', 'Shift updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->back()->with('success', 'Shift deleted successfully');
    }
}
