<?php

namespace App\Http\Middleware;

use Closure;
use App\Support\SiteRenewal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('admin.login.form')
                ->with('error', 'Admin access required. Please login.');
        }

        if (
            SiteRenewal::isExpired()
            && !Auth::user()->isSuperAdmin()
            && !$request->routeIs('admin.dashboard')
            && !$request->routeIs('admin.logout')
        ) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Website renewal is expired. Please contact super admin.');
        }

        return $next($request);
    }
}
