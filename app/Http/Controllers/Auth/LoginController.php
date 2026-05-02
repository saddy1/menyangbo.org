<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create(Request $request)
    {
        $next = $request->query('next');
        if (is_string($next) && $this->isSafeReturnUrl($next)) {
            $request->session()->put('url.intended', $next);
        }

        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'इमेल वा पासवर्ड मिलेन।',
            ])->onlyInput('email');
        }

        $next = $request->input('next');
        if (is_string($next) && $this->isSafeReturnUrl($next)) {
            $request->session()->put('url.intended', $next);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        $fallback = $user->isAdmin() ? route('admin.dashboard') : route('home');

        return redirect()->intended($fallback);
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function isSafeReturnUrl(string $url): bool
    {
        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//');
        }

        $host = parse_url($url, PHP_URL_HOST);
        return $host && $host === request()->getHost();
    }
}
