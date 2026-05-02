@extends('admin.layout')
@section('title', 'User Management')

@section('content')
<div class="py-6">

  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">User Management</h1>
      <p class="text-sm text-gray-500 mt-1">
        Manage roles for all users.
        @if(auth()->user()->isSuperAdmin())
          Super Admins can create new admins and change any role.
        @else
          Role changes are restricted to Super Admins.
        @endif
      </p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← Dashboard</a>
  </div>

  {{-- Flash messages --}}
  @if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-green-800 text-sm font-semibold flex items-center gap-2">
      <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      {{ session('success') }}
    </div>
  @endif
  @if(session('error'))
    <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm font-semibold">
      {{ session('error') }}
    </div>
  @endif
  @if($errors->any())
    <div class="mb-4 rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-rose-800 text-sm">
      <div class="font-semibold mb-1">Please fix the following:</div>
      <ul class="list-disc ml-4 space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="grid grid-cols-1 {{ auth()->user()->isSuperAdmin() ? 'xl:grid-cols-3' : '' }} gap-6">

    {{-- ── Left: User table ── --}}
    <div class="{{ auth()->user()->isSuperAdmin() ? 'xl:col-span-2' : '' }}">

      {{-- Role legend --}}
      <div class="flex gap-2 mb-4 flex-wrap">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
          ★ Super Admin
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
          ◆ Admin
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
          ● Member
        </span>
      </div>

      {{-- Filters --}}
      <form method="GET" class="flex gap-2 mb-4 flex-wrap items-center">
        <input type="text" name="search" value="{{ request('search') }}"
          placeholder="Search name or email…"
          class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400 w-56">
        <select name="role" class="border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-400">
          <option value="">All Roles</option>
          <option value="super_admin" @selected(request('role') === 'super_admin')>Super Admin</option>
          <option value="admin"       @selected(request('role') === 'admin')>Admin</option>
          <option value="member"      @selected(request('role') === 'member')>Member</option>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
          Filter
        </button>
        @if(request('search') || request('role'))
          <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
        @endif
        <span class="ml-auto text-xs text-gray-400">{{ $users->total() }} users</span>
      </form>

      {{-- Table --}}
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left">User</th>
              <th class="px-4 py-3 text-left">Role</th>
              <th class="px-4 py-3 text-left hidden sm:table-cell">Verified</th>
              <th class="px-4 py-3 text-left hidden md:table-cell">Joined</th>
              @if(auth()->user()->isSuperAdmin())
                <th class="px-4 py-3 text-left">Actions</th>
              @endif
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
              <tr class="hover:bg-gray-50/60 transition-colors {{ $user->id === auth()->id() ? 'bg-blue-50/30' : '' }}">

                {{-- User info --}}
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    @if($user->avatar)
                      <img src="{{ $user->avatar }}" alt="" class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-gray-200">
                    @else
                      <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold text-white
                        {{ $user->isSuperAdmin() ? 'bg-purple-500' : ($user->isAdmin() ? 'bg-blue-500' : 'bg-gray-400') }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                      </div>
                    @endif
                    <div class="min-w-0">
                      <div class="font-semibold text-gray-900 truncate">
                        {{ $user->name }}
                        @if($user->id === auth()->id())
                          <span class="text-xs text-blue-500 font-normal">(you)</span>
                        @endif
                      </div>
                      <div class="text-xs text-gray-400 truncate">{{ $user->email }}</div>
                      @if($user->google_id)
                        <div class="text-[10px] text-gray-400">🔗 Google</div>
                      @endif
                    </div>
                  </div>
                </td>

                {{-- Role badge --}}
                <td class="px-4 py-3">
                  @php
                    $cls = match($user->role) {
                      'super_admin' => 'bg-purple-100 text-purple-800 border-purple-200',
                      'admin'       => 'bg-blue-100 text-blue-800 border-blue-200',
                      default       => 'bg-gray-100 text-gray-600 border-gray-200',
                    };
                    $icon = match($user->role) {
                      'super_admin' => '★',
                      'admin'       => '◆',
                      default       => '●',
                    };
                  @endphp
                  <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $cls }}">
                    {{ $icon }} {{ $user->roleBadge() }}
                  </span>
                </td>

                {{-- Verified --}}
                <td class="px-4 py-3 hidden sm:table-cell">
                  @if($user->email_verified_at)
                    <span class="text-green-600 text-xs font-semibold">✓ Yes</span>
                  @else
                    <span class="text-amber-500 text-xs">⚠ No</span>
                  @endif
                </td>

                {{-- Joined --}}
                <td class="px-4 py-3 text-gray-400 text-xs hidden md:table-cell">
                  {{ $user->created_at->format('d M Y') }}
                </td>

                {{-- Actions (super admin only) --}}
                @if(auth()->user()->isSuperAdmin())
                <td class="px-4 py-3">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    {{-- Quick role change --}}
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-1">
                      @csrf @method('PATCH')
                      <select name="role"
                        class="border border-gray-200 rounded-lg px-2 py-1 text-xs bg-white focus:ring-1 focus:ring-blue-400 outline-none">
                        <option value="member"      @selected($user->role === 'member')>Member</option>
                        <option value="admin"       @selected($user->role === 'admin')>Admin</option>
                        <option value="super_admin" @selected($user->role === 'super_admin')>Super Admin</option>
                      </select>
                      <button type="submit"
                        class="bg-blue-600 text-white px-2.5 py-1 rounded-lg text-xs font-semibold hover:bg-blue-700 transition whitespace-nowrap">
                        Save
                      </button>
                    </form>

                    {{-- Delete --}}
                    @if($user->id !== auth()->id())
                      <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                        onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                        @csrf @method('DELETE')
                        <button type="submit"
                          class="text-red-500 hover:text-red-700 text-xs font-semibold px-2 py-1 rounded hover:bg-red-50 transition whitespace-nowrap">
                          Remove
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
                @endif

              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">No users found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($users->hasPages())
        <div class="mt-4">{{ $users->links() }}</div>
      @endif
    </div>

    {{-- ── Right: Create Admin panel (super_admin only) ── --}}
    @if(auth()->user()->isSuperAdmin())
    <div class="xl:col-span-1">
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 sticky top-6">

        <div class="flex items-center gap-2 mb-5">
          <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
            <svg class="w-4 h-4 text-purple-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-bold text-gray-800">Create Admin Account</h2>
            <p class="text-xs text-gray-500">Super Admin only</p>
          </div>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
          @csrf

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
              placeholder="e.g. Ram Bahadur"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
              placeholder="admin@example.com"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Role <span class="text-red-500">*</span></label>
            <select name="role" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-400 bg-white">
              <option value="admin"       @selected(old('role', 'admin') === 'admin')>◆ Admin</option>
              <option value="super_admin" @selected(old('role') === 'super_admin')>★ Super Admin</option>
            </select>
            <p class="text-[11px] text-gray-400 mt-1">Members register themselves. Only admins are created here.</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
            <input type="password" name="password" required
              placeholder="Min. 8 characters"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
          </div>

          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Confirm Password <span class="text-red-500">*</span></label>
            <input type="password" name="password_confirmation" required
              placeholder="Repeat password"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-purple-400 focus:border-purple-400">
          </div>

          <button type="submit"
            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2.5 rounded-xl transition text-sm flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Create Admin
          </button>
        </form>

        {{-- Quick tip --}}
        <div class="mt-5 rounded-xl bg-gray-50 border border-gray-200 p-4 text-xs text-gray-500 space-y-1">
          <div class="font-semibold text-gray-700 mb-1">Role summary</div>
          <div><span class="font-semibold text-purple-700">★ Super Admin</span> — full access + create/delete admins + change roles</div>
          <div><span class="font-semibold text-blue-700">◆ Admin</span> — manage persons, requests, content; cannot manage users</div>
          <div><span class="font-semibold text-gray-600">● Member</span> — public site, can submit change requests</div>
        </div>
      </div>
    </div>
    @endif

  </div>
</div>
@endsection
