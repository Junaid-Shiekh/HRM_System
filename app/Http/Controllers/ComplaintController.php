<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    private ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'perPage']);
        $complaints = $this->complaintService->list($filters);

        return Inertia::render('Complaints/Index', [
            'complaints' => $complaints,
            'filters' => $filters,
            'isHR' => !\Illuminate\Support\Facades\Auth::user()->hasRole('employee'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $this->complaintService->create($validated);

        return redirect()->back()->with('success', 'Complaint submitted successfully.');
    }

    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,progress,done',
            'resolution_note' => 'nullable|string',
        ]);

        $this->complaintService->update($complaint, $validated);

        return redirect()->back()->with('success', 'Complaint status updated successfully.');
    }

    public function destroy(Complaint $complaint)
    {
        $this->complaintService->delete($complaint);
        return redirect()->back()->with('success', 'Complaint deleted successfully.');
    }
}
