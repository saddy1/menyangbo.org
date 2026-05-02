<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function requireSuperAdmin(): void
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403, 'Super Admin access required.');
        }
    }

    public function index(Request $request)
    {
        $query = User::query()->orderByRaw("FIELD(role,'super_admin','admin','member')")->orderBy('name');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(30)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // ── Create new admin (super_admin only) ────────────────────────────────
    public function store(Request $request)
    {
        $this->requireSuperAdmin();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:admin,super_admin'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'password'          => Hash::make($data['password']),
            'role'              => $data['role'],
            'email_verified_at' => now(), // admins are pre-verified
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name} created as {$user->roleBadge()} successfully.");
    }

    // ── Update role of existing user ───────────────────────────────────────
    public function updateRole(Request $request, User $user)
    {
        $this->requireSuperAdmin();

        $request->validate([
            'role' => ['required', 'in:member,admin,super_admin'],
        ]);

        if ($user->id === Auth::id() && $request->role !== User::ROLE_SUPER_ADMIN) {
            return back()->with('error', 'You cannot demote your own account.');
        }

        $old = $user->roleBadge();
        $user->update(['role' => $request->role]);

        return back()->with('success', "{$user->name} changed from {$old} → {$user->roleBadge()}.");
    }

    // ── Delete a user ──────────────────────────────────────────────────────
    public function destroy(Request $request, User $user)
    {
        $this->requireSuperAdmin();

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "{$name} has been removed.");
    }
}
