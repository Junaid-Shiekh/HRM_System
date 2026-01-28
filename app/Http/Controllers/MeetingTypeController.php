<?php

namespace App\Http\Controllers;

use App\Models\MeetingType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingTypeController extends Controller
{
    public function index()
    {
        $types = MeetingType::withCount('meetings')->orderBy('id', 'desc')->get();
        return Inertia::render('MeetingTypes/Index', [
            'meetingTypes' => $types
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'default_duration' => 'required|integer|min:15',
            'status' => 'required|string',
        ]);

        MeetingType::create($request->all());

        return redirect()->back()->with('success', 'Meeting Type created successfully.');
    }

    public function update(Request $request, MeetingType $meetingType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|max:7',
            'default_duration' => 'required|integer|min:15',
            'status' => 'required|string',
        ]);

        $meetingType->update($request->all());

        return redirect()->back()->with('success', 'Meeting Type updated successfully.');
    }

    public function destroy(MeetingType $meetingType)
    {
        $meetingType->delete();
        return redirect()->back()->with('success', 'Meeting Type deleted successfully.');
    }
}
