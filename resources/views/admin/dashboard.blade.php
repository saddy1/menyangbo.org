@extends('admin.layout')
@section('title','Admin • Dashboard')
@section('content')
<div class="py-6 space-y-8">

  {{-- ── Welcome bar ── --}}
  <div class="flex items-center justify-between flex-wrap gap-3">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
      <p class="text-sm text-gray-500 mt-0.5">Welcome back, <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span></p>
    </div>
    <a href="{{ route('admin.persons.create') }}"
      class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
      <i class="fa fa-plus"></i> Add Person
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 rounded-2xl border {{ ($siteExpired ?? false) ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }} p-5">
      <div class="flex items-center justify-between gap-3 flex-wrap">
        <div>
          <div class="text-xs font-bold uppercase tracking-wider {{ ($siteExpired ?? false) ? 'text-red-600' : 'text-emerald-700' }}">Website Renewal</div>
          <div class="mt-1 text-lg font-extrabold text-gray-900">
            @if($renewDate)
              {{ $renewDate->format('Y-m-d') }}
            @else
              Not set
            @endif
          </div>
        </div>
        <div class="text-right">
          @if($siteExpired ?? false)
            <div class="text-2xl font-black text-red-700">Expired</div>
            <div class="text-xs text-red-500">Admins are locked until renewal.</div>
          @elseif(!is_null($renewDaysLeft))
            <div class="text-2xl font-black text-emerald-700">{{ $renewDaysLeft }}</div>
            <div class="text-xs text-emerald-600">days left</div>
          @else
            <div class="text-sm font-bold text-gray-500">No countdown</div>
          @endif
        </div>
      </div>
    </div>

    @if(auth()->user()->isSuperAdmin())
      <form method="POST" action="{{ route('admin.renewal.update') }}" class="rounded-2xl border border-purple-200 bg-white p-5 shadow-sm">
        @csrf
        <label class="block text-xs font-bold uppercase tracking-wider text-purple-700 mb-2">Set renew date</label>
        <div class="flex gap-2">
          <input type="date" name="renew_until" value="{{ $renewDate?->format('Y-m-d') }}" required
            class="min-w-0 flex-1 rounded-xl border-gray-300 text-sm focus:border-purple-500 focus:ring-purple-500">
          <button class="rounded-xl bg-purple-600 px-4 py-2 text-sm font-bold text-white hover:bg-purple-700">Save</button>
        </div>
      </form>
    @endif
  </div>

  {{-- ── Stats grid ── --}}
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

    <a href="{{ route('admin.persons.index') }}"
      class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:border-blue-400 hover:shadow-md transition-all">
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
          <i class="fas fa-users text-blue-600"></i>
        </div>
        <i class="fas fa-arrow-right text-gray-300 text-xs group-hover:text-blue-400 transition"></i>
      </div>
      <div class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['people']) }}</div>
      <div class="text-xs text-gray-500 mt-0.5 font-medium">Total Members</div>
      <div class="text-[11px] text-gray-400 mt-1">
        {{ $stats['male'] }} male · {{ $stats['female'] }} female
      </div>
    </a>

    <a href="{{ route('admin.persons.index') }}"
      class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:border-pink-400 hover:shadow-md transition-all">
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center">
          <i class="fas fa-venus-mars text-pink-600"></i>
        </div>
        <i class="fas fa-arrow-right text-gray-300 text-xs group-hover:text-pink-400 transition"></i>
      </div>
      <div class="grid grid-cols-2 gap-x-3 gap-y-1 text-sm">
        <div><span class="font-extrabold text-blue-700">{{ number_format($stats['male']) }}</span><span class="text-gray-500 text-xs ml-1">Male</span></div>
        <div><span class="font-extrabold text-pink-700">{{ number_format($stats['female']) }}</span><span class="text-gray-500 text-xs ml-1">Female</span></div>
        <div><span class="font-extrabold text-purple-700">{{ number_format($stats['other']) }}</span><span class="text-gray-500 text-xs ml-1">Other</span></div>
        <div><span class="font-extrabold text-gray-700">{{ number_format($stats['unknown']) }}</span><span class="text-gray-500 text-xs ml-1">Unknown</span></div>
      </div>
      <div class="text-xs text-gray-500 mt-2 font-medium">Gender Breakdown</div>
    </a>

    <a href="{{ route('admin.relationships.index') }}"
      class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:border-indigo-400 hover:shadow-md transition-all">
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
          <i class="fas fa-sitemap text-indigo-600"></i>
        </div>
        <i class="fas fa-arrow-right text-gray-300 text-xs group-hover:text-indigo-400 transition"></i>
      </div>
      <div class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['edges']) }}</div>
      <div class="text-xs text-gray-500 mt-0.5 font-medium">Relationships</div>
    </a>

    <a href="{{ route('admin.unions.index') }}"
      class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:border-rose-400 hover:shadow-md transition-all">
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
          <i class="fas fa-heart text-rose-500"></i>
        </div>
        <i class="fas fa-arrow-right text-gray-300 text-xs group-hover:text-rose-400 transition"></i>
      </div>
      <div class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['unions']) }}</div>
      <div class="text-xs text-gray-500 mt-0.5 font-medium">Unions</div>
    </a>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
          <i class="fas fa-cross text-gray-500"></i>
        </div>
      </div>
      <div class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['deceased']) }}</div>
      <div class="text-xs text-gray-500 mt-0.5 font-medium">Deceased</div>
      <div class="text-[11px] text-gray-400 mt-1">
        {{ $stats['people'] > 0 ? round($stats['deceased'] / $stats['people'] * 100) : 0 }}% of total
      </div>
    </div>

    <a href="{{ route('admin.requests.index') }}"
      class="group bg-white rounded-2xl border border-gray-200 shadow-sm p-5 hover:border-amber-400 hover:shadow-md transition-all relative overflow-hidden">
      @if($stats['pending_req'] > 0)
        <div class="absolute top-3 right-3 w-5 h-5 bg-amber-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold">
          {{ $stats['pending_req'] > 9 ? '9+' : $stats['pending_req'] }}
        </div>
      @endif
      <div class="flex items-center justify-between mb-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
          <i class="fas fa-list-check text-amber-600"></i>
        </div>
      </div>
      <div class="text-3xl font-extrabold text-gray-800">{{ number_format($stats['total_req']) }}</div>
      <div class="text-xs text-gray-500 mt-0.5 font-medium">Change Requests</div>
      <div class="text-[11px] mt-1 {{ $stats['pending_req'] > 0 ? 'text-amber-600 font-semibold' : 'text-gray-400' }}">
        {{ $stats['pending_req'] }} pending
      </div>
    </a>

  </div>

  {{-- ── Two-column: Pending Requests + Recent Users ── --}}
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Pending requests --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i class="fas fa-clock text-amber-500"></i>
          <span class="font-semibold text-gray-800 text-sm">Pending Change Requests</span>
          @if($stats['pending_req'] > 0)
            <span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">{{ $stats['pending_req'] }}</span>
          @endif
        </div>
        <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}"
          class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View all →</a>
      </div>

      @if($recentRequests->isEmpty())
        <div class="px-5 py-10 text-center text-gray-400 text-sm">
          <i class="fas fa-check-circle text-3xl text-green-300 mb-2 block"></i>
          No pending requests. All clear!
        </div>
      @else
        <ul class="divide-y divide-gray-50">
          @foreach($recentRequests as $req)
            <li class="px-5 py-3 hover:bg-gray-50 transition-colors">
              <a href="{{ route('admin.requests.show', $req->id) }}" class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5
                  {{ match($req->type) {
                    'add_child'      => 'bg-blue-100 text-blue-600',
                    'mark_deceased'  => 'bg-gray-100 text-gray-600',
                    'update_profile' => 'bg-purple-100 text-purple-600',
                    'add_union'      => 'bg-rose-100 text-rose-500',
                    default          => 'bg-gray-100 text-gray-500',
                  } }}">
                  <i class="fas {{ match($req->type) {
                    'add_child'      => 'fa-child',
                    'mark_deceased'  => 'fa-cross',
                    'update_profile' => 'fa-pen',
                    'add_union'      => 'fa-heart',
                    default          => 'fa-file',
                  } }} text-xs"></i>
                </div>
                <div class="min-w-0 flex-1">
                  <div class="text-sm font-semibold text-gray-800 truncate">
                    {{ ucfirst(str_replace('_', ' ', $req->type)) }}
                    @if($req->person) — {{ $req->person->display_name }} @endif
                  </div>
                  <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                    <span>By {{ $req->submitted_name ?? $req->user?->name ?? 'Unknown' }}</span>
                    <span>·</span>
                    <span>{{ $req->created_at->diffForHumans() }}</span>
                  </div>
                </div>
                <span class="text-xs text-blue-500 flex-shrink-0 self-center">→</span>
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    </div>

    {{-- Recent users --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i class="fas fa-user-plus text-blue-500"></i>
          <span class="font-semibold text-gray-800 text-sm">Recent Members</span>
          <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">{{ $stats['users'] }}</span>
        </div>
        <a href="{{ route('admin.users.index') }}"
          class="text-xs text-blue-600 hover:text-blue-800 font-semibold">View all →</a>
      </div>

      <ul class="divide-y divide-gray-50">
        @foreach($recentUsers as $u)
          <li class="px-5 py-3 flex items-center gap-3">
            @if($u->avatar)
              <img src="{{ $u->avatar }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0" alt="">
            @else
              <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold text-white
                {{ $u->isSuperAdmin() ? 'bg-purple-500' : ($u->isAdmin() ? 'bg-blue-500' : 'bg-gray-400') }}">
                {{ strtoupper(mb_substr($u->name, 0, 1)) }}
              </div>
            @endif
            <div class="min-w-0 flex-1">
              <div class="text-sm font-semibold text-gray-800 truncate">{{ $u->name }}</div>
              <div class="text-xs text-gray-400 truncate">{{ $u->email }}</div>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0
              {{ $u->isSuperAdmin() ? 'bg-purple-100 text-purple-700' : ($u->isAdmin() ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
              {{ $u->roleBadge() }}
            </span>
          </li>
        @endforeach
      </ul>
    </div>

  </div>

  {{-- ── Quick actions ── --}}
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
    <h2 class="text-sm font-semibold text-gray-700 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      @php
        $actions = [
          ['route' => 'admin.persons.create',  'icon' => 'fa-user-plus',       'label' => 'Add Person',     'color' => 'blue'],
          ['route' => 'admin.requests.index',  'icon' => 'fa-list-check',      'label' => 'Requests',       'color' => 'amber'],
          ['route' => 'admin.gallery.index',   'icon' => 'fa-images',          'label' => 'Gallery',        'color' => 'purple'],
          ['route' => 'admin.notices.index',   'icon' => 'fa-bell',            'label' => 'Notices',        'color' => 'rose'],
          ['route' => 'admin.events.index',    'icon' => 'fa-calendar-days',   'label' => 'Events',         'color' => 'green'],
          ['url' => route('tree.index', ['export' => 1]), 'icon' => 'fa-print', 'label' => 'Tree Export', 'color' => 'gray'],
        ];
        $colorMap = [
          'blue'   => 'bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-100',
          'amber'  => 'bg-amber-50 text-amber-700 hover:bg-amber-100 border-amber-100',
          'purple' => 'bg-purple-50 text-purple-700 hover:bg-purple-100 border-purple-100',
          'rose'   => 'bg-rose-50 text-rose-700 hover:bg-rose-100 border-rose-100',
          'green'  => 'bg-green-50 text-green-700 hover:bg-green-100 border-green-100',
          'gray'   => 'bg-gray-50 text-gray-700 hover:bg-gray-100 border-gray-100',
        ];
      @endphp
      @foreach($actions as $action)
        <a href="{{ $action['url'] ?? route($action['route']) }}"
          class="flex flex-col items-center gap-2 p-4 rounded-xl border text-center transition-all {{ $colorMap[$action['color']] }}">
          <i class="fas {{ $action['icon'] }} text-xl"></i>
          <span class="text-xs font-semibold">{{ $action['label'] }}</span>
        </a>
      @endforeach
    </div>
  </div>

</div>
@endsection
