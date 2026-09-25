<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return redirect()->route('home');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect()->route('home')->with('success', __('इमेल सफलतापूर्वक प्रमाणित भयो!'));
    }

    public function resend(Request $request)
    {
        return redirect()->route('home');
    }
}
