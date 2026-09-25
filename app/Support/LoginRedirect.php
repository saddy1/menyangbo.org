<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

/** Where to send someone right after they log in. */
class LoginRedirect
{
    /**
     * Admins land on the dashboard (or the admin page they were trying to open);
     * members go back to the page they came from, else home.
     */
    public static function for(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            $intended = session()->pull('url.intended');
            $path = is_string($intended) ? (string) parse_url($intended, PHP_URL_PATH) : '';

            return redirect(preg_match('~^/admin(/|$)~', $path) ? $intended : route('admin.dashboard'));
        }

        return redirect()->intended(route('home'));
    }
}
