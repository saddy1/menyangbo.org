<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // already logged in (e.g. opened /login again): admins → dashboard
                $user = Auth::guard($guard)->user();
                return $user instanceof \App\Models\User
                    ? \App\Support\LoginRedirect::for($user)
                    : redirect()->intended(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}
