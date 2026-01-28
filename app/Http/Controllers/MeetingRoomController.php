<?php

namespace App\Http\Controllers;

use App\Models\MeetingRoom;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeetingRoomController extends Controller
{
    public function index()
    {
        $rooms = MeetingRoom::withCount('meetings')->orderBy('id', 'desc')->get();
        return Inertia::render('MeetingRooms/Index', [
            'meetingRooms' => $rooms
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'equipment' => 'nullable|string',
            'status' => 'required|string',
        ]);

        MeetingRoom::create($request->all());

        return redirect()->back()->with('success', 'Meeting Room created successfully.');
    }

    public function update(Request $request, MeetingRoom $meetingRoom)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'equipment' => 'nullable|string',
            'status' => 'required|string',
        ]);

        $meetingRoom->update($request->all());

        return redirect()->back()->with('success', 'Meeting Room updated successfully.');
    }

    public function destroy(MeetingRoom $meetingRoom)
    {
        $meetingRoom->delete();
        return redirect()->back()->with('success', 'Meeting Room deleted successfully.');
    }
}
