<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\Media;
use App\Models\ParentChildEdge;
use App\Models\Person;
use App\Models\PersonChangeRequest;
use App\Models\PopupNotice;
use App\Models\PostEvent;
use App\Models\SiteSetting;
use App\Models\UnionModel;
use App\Models\User;
use App\Support\SiteRenewal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function home()
    {
        $genderCounts = Person::query()
            ->selectRaw("gender, COUNT(*) as total")
            ->groupBy('gender')
            ->pluck('total', 'gender');

        $stats = [
            'people'      => Person::count(),
            'generations' => Person::whereNotNull('pusta')->distinct()->count('pusta'),
            'unions'      => UnionModel::count(),
            'deceased'    => Person::where('is_deceased', true)->count(),
            'male'        => (int) ($genderCounts['male'] ?? 0),
            'female'      => (int) ($genderCounts['female'] ?? 0),
            'other'       => (int) ($genderCounts['other'] ?? 0),
            'unknown'     => (int) ($genderCounts['unknown'] ?? 0),
        ];

        $events        = PostEvent::where('is_active', true)->orderByDesc('event_date')->limit(6)->get();
        $gallery       = Media::latest()->limit(8)->get();
        $popup         = PostEvent::where('is_active', true)->where('show_popup', true)->latest()->first();
        $popups        = PopupNotice::where('is_active', true)->latest()->get();
        $homeSections  = HomeSection::where('is_active', true)->orderBy('sort_order')->get()->groupBy('section');

        return view('welcome', compact('stats', 'events', 'gallery', 'popup', 'popups', 'homeSections'));
    }

    public function showadminform()
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, true)) {
            return back()->withInput()->with('error', 'Email or password is incorrect.');
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            return back()->withInput()->with('error', 'You do not have admin access.');
        }

        $request->session()->regenerate();
        return redirect()->route('admin.dashboard');
    }

    public function index()
    {
        $genderCounts = Person::query()
            ->selectRaw("gender, COUNT(*) as total")
            ->groupBy('gender')
            ->pluck('total', 'gender');

        $stats = [
            'people'      => Person::count(),
            'edges'       => ParentChildEdge::count(),
            'unions'      => UnionModel::count(),
            'deceased'    => Person::where('is_deceased', true)->count(),
            'male'        => (int) ($genderCounts['male'] ?? 0),
            'female'      => (int) ($genderCounts['female'] ?? 0),
            'other'       => (int) ($genderCounts['other'] ?? 0),
            'unknown'     => (int) ($genderCounts['unknown'] ?? 0),
            'pending_req' => PersonChangeRequest::where('status', 'pending')->count(),
            'total_req'   => PersonChangeRequest::count(),
            'users'       => User::count(),
            'admins'      => User::whereIn('role', ['admin', 'super_admin'])->count(),
        ];

        $recentRequests = PersonChangeRequest::with(['person:id,display_name', 'user:id,name'])
            ->where('status', 'pending')
            ->latest()
            ->limit(6)
            ->get();

        $recentUsers = User::latest()->limit(5)->get();
        $renewDate = SiteRenewal::date();
        $renewDaysLeft = SiteRenewal::daysLeft();
        $siteExpired = SiteRenewal::isExpired();

        return view('admin.dashboard', compact('stats', 'recentRequests', 'recentUsers', 'renewDate', 'renewDaysLeft', 'siteExpired'));
    }

    public function updateRenewal(Request $request)
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'Super admin only.');
        }

        $data = $request->validate([
            'renew_until' => ['required', 'date'],
        ]);

        SiteSetting::setValue(SiteRenewal::KEY, $data['renew_until']);

        return back()->with('success', 'Renew date updated.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
