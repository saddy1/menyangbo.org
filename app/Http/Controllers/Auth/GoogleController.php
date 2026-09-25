<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request)
    {
        if (empty(config('services.google.client_id'))) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google login is not configured. Please contact the administrator.']);
        }

        $next = $request->query('next');
        if (is_string($next) && $this->isSafeReturnUrl($next)) {
            $request->session()->put('url.intended', $next);
        }

        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        if (empty(config('services.google.client_id'))) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google login is not configured.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google login failed. Please try again.']);
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google did not return an email address. Please use another Google account.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill([
                'google_id'         => $user->google_id ?: $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar() ?: $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        } else {
            // New user via Google — role defaults to 'member'
            $user = User::create([
                'name'              => $googleUser->getName() ?: $email,
                'email'             => $email,
                'password'          => null,
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'role'              => User::ROLE_MEMBER,
                'email_verified_at' => now(), // Google already verified the email
            ]);

            event(new Registered($user));
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        return \App\Support\LoginRedirect::for($user);
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
