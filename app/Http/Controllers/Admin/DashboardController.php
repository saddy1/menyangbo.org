<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\ParentChildEdge;
use App\Models\UnionModel;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class DashboardController extends Controller
{
public function home()
{
    return view('welcome');
}

    public function showadminform()
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }


    public function adminLogin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);


        $admin = Admin::where('email', $request->email)->first();
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return back()->withInput()->with('error', 'Invalid credentials.');
        }


        session(['admin_id' => $admin->id]);
        return redirect()->route('admin.dashboard');
    }

    public function index()
    {
        $stats = [
            'people'   => Person::count(),
            'edges'    => ParentChildEdge::count(),
            'unions'   => UnionModel::count(),
            'deceased' => Person::where('is_deceased', true)->count(),

        ];
                $admin = Admin::find(session(key: 'admin_id'));


        return view('admin.dashboard', compact('stats','admin'));
    }

     public function logout()
    {
        session()->forget(['admin_id']);
        return redirect()->route('home')->with('success', 'Logged out successfully.');
    }
}
