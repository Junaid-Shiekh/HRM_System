<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles', 'permissions') : null,
            ],
            'applicantCount' => $request->user() ? \App\Models\JobApplication::where('status', 'applied')->count() : 0,
            'pendingComplaintCount' => $request->user() ? \App\Models\Complaint::where('status', 'pending')->count() : 0,
            'pendingWarningCount' => $request->user() ? (\App\Models\Warning::when($request->user()->hasRole('employee'), function($q) use ($request) {
                return $q->whereHas('employee', fn($sq) => $sq->where('user_id', $request->user()->id));
            })->where('status', 'pending')->count()) : 0,
            'pendingLoanCount' => $request->user() ? \App\Models\Loan::where('status', 'pending')->count() : 0,
            'pendingAdvanceCount' => $request->user() ? \App\Models\SalaryAdvance::where('status', 'pending')->count() : 0,
        ];

    }
}
