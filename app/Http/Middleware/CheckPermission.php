<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If not logged in, let it pass (auth middleware should handle this)
        if (!$user) {
            return $next($request);
        }

        // Super Admin and HR have all permissions
        if ($user->hasRole('Super Admin') || $user->hasRole('hr')) {
            return $next($request);
        }

        $routeName = Route::currentRouteName();

        // If route has no name, let it pass or handle explicitly
        if (!$routeName) {
            return $next($request);
        }

        // Exclude common routes
        $excludedRoutes = [
            'dashboard',
            'profile.edit',
            'profile.update',
            'profile.destroy',
            'logout',
            'website.home',
            'website.about',
            'website.jobs',
            'website.jobs.apply',
            'login',
            'employee.dashboard',
        ];



        if (in_array($routeName, $excludedRoutes)) {
            return $next($request);
        }

        // Check if user has permission
        if (!$user->can($routeName)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }
            abort(403, 'UNAUTHORIZED');
        }

        return $next($request);
    }
}
