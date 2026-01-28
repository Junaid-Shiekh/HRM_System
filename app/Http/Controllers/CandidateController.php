<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Services\CandidateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CandidateController extends Controller
{
    private CandidateService $candidateService;

    public function __construct(CandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    public function index(Request $request): Response
    {
        $filters = $request->only(['search']);
        $candidates = $this->candidateService->list($filters);

        return Inertia::render('Recruitment/Candidates/Index', [
            'candidates' => $candidates,
            'filters' => $filters,
        ]);
    }

    public function store(StoreCandidateRequest $request)
    {
        $this->candidateService->create($request->validated());
        return redirect()->back()->with('success', 'Candidate created successfully.');
    }

    public function update(UpdateCandidateRequest $request, Candidate $candidate)
    {
        $this->candidateService->update($candidate, $request->validated());
        return redirect()->back()->with('success', 'Candidate updated successfully.');
    }

    public function destroy(Candidate $candidate)
    {
        $this->candidateService->delete($candidate);
        return redirect()->back()->with('success', 'Candidate deleted successfully.');
    }
}
