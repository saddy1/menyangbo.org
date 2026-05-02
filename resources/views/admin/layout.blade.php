<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title','Portal')</title>
  <link rel="icon" type="image/x-icon" href="{{asset('menyanbo_logo.png')}}">
  @vite('resources/css/app.css')
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <style>
    [x-cloak]{display:none!important}
    @keyframes renewPulse { 0%,100%{opacity:.72;transform:scale(1)} 50%{opacity:1;transform:scale(1.015)} }
  </style>
</head>

<body class="antialiased bg-gray-50 text-gray-900">

  <!-- HEADER -->
  <header class="fixed inset-x-0 top-0 h-16 bg-white shadow-lg z-50">
    <div class="h-full px-4 md:px-6 flex items-center justify-between">
      <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
        <img src="{{ asset('menyanbo_logo.png') }}" class="h-10 w-auto" alt="Logo">
        <span class="text-lg md:text-xl font-bold text-blue-900">Menyanbo Admin</span>
      </a>

      <div class="flex items-center gap-2">
        <!-- Sidebar toggle -->
        <button id="sidebarToggle"
          class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100" title="Menu">
          <i class="fa-solid fa-bars text-lg"></i>
        </button>

        @php $authUser = auth()->user(); @endphp
        <div class="relative">
          <button id="adminBellBtn" class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100" title="Notifications">
            <i class="fa-solid fa-bell text-gray-700"></i>
            @if(($adminNotificationCount ?? 0) > 0)
              <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center">
                {{ $adminNotificationCount > 99 ? '99+' : $adminNotificationCount }}
              </span>
            @endif
          </button>
          <div id="adminBellMenu" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 font-semibold text-sm text-gray-800">New activity</div>
            <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 text-sm">
              <span><i class="fa-solid fa-list-check text-amber-500 mr-2"></i>Pending requests</span>
              <span class="font-bold text-amber-700">{{ $pendingRequestsCount ?? 0 }}</span>
            </a>
            <a href="{{ route('admin.feedback.index') }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 text-sm border-t">
              <span><i class="fa-solid fa-comments text-blue-500 mr-2"></i>Unread feedback</span>
              <span class="font-bold text-blue-700">{{ $unreadFeedbackCount ?? 0 }}</span>
            </a>
          </div>
        </div>

        <!-- Profile dropdown -->
        <div class="relative">
          <button id="profileDropdownBtn" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-100">
            @if($authUser?->avatar)
              <img src="{{ $authUser->avatar }}" class="w-8 h-8 rounded-full object-cover" alt="">
            @else
              <div class="w-8 h-8 rounded-full flex items-center justify-center font-semibold text-white text-sm
                {{ $authUser?->isSuperAdmin() ? 'bg-purple-600' : 'bg-blue-600' }}">
                {{ strtoupper(mb_substr($authUser?->name ?? 'A', 0, 1)) }}
              </div>
            @endif
            <div class="hidden md:block text-left">
              <div class="font-medium text-sm leading-tight">{{ $authUser?->name }}</div>
              <div class="text-[11px] text-gray-500 leading-tight">{{ $authUser?->roleBadge() }}</div>
            </div>
            <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
          </button>
          <div id="profileDropdownMenu"
            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
              <div class="text-sm font-semibold text-gray-800 truncate">{{ $authUser?->name }}</div>
              <div class="text-xs text-gray-400 truncate">{{ $authUser?->email }}</div>
              <div class="mt-1">
                @php
                  $badgeCls = $authUser?->isSuperAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700';
                @endphp
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $badgeCls }}">
                  {{ $authUser?->roleBadge() }}
                </span>
              </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="p-1">
              @csrf
              <button type="submit"
                class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                <i class="fa-solid fa-right-from-bracket w-4"></i> Logout
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- SIDEBAR -->
  <div id="sidebarBackdrop" class="hidden fixed inset-0 bg-black/30 z-40 lg:hidden"></div>
  <aside id="sidebar" class="fixed left-0 top-16 w-64 h-[calc(100vh-4rem)] bg-gray-900 text-white z-50
                transform -translate-x-full transition-transform duration-300
                overflow-y-auto lg:translate-x-0">
    <nav class="py-4">
      <ul class="space-y-1 px-3 text-sm">
        <li>
          <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fas fa-gauge-high w-5 text-gray-300"></i><span>Dashboard</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.persons.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fas fa-user-group w-5 text-gray-300"></i><span>Persons</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.relationships.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fas fa-sitemap w-5 text-gray-300"></i><span>Relationships</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.unions.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fa fa-heart-o w-5 text-gray-300"></i><span>Unions</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.feedback.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fa-solid fa-comments w-5 text-gray-300"></i><span>Feedback</span>
            @if(($unreadFeedbackCount ?? 0) > 0)
              <span class="ml-auto min-w-5 h-5 px-1 rounded-full bg-blue-600 text-white text-[10px] font-bold flex items-center justify-center">{{ $unreadFeedbackCount }}</span>
            @endif
          </a>
        </li>
        <li>
          <a href="{{ route('admin.requests.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fa-solid fa-list w-5 text-gray-300"></i><span>Change Requests</span>
            @if(($pendingRequestsCount ?? 0) > 0)
              <span class="ml-auto min-w-5 h-5 px-1 rounded-full bg-amber-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $pendingRequestsCount }}</span>
            @endif
          </a>
        </li>

        <li class="pt-3">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Tools</p>
        </li>
        <li>
          <a href="{{ route('tree.index', ['export' => 1]) }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fa-solid fa-print w-5 text-gray-300"></i><span>Tree Export</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.users.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.users.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-users w-5 text-gray-300"></i>
            <span>Users</span>
            @if(auth()->user()?->isSuperAdmin())
              <span class="ml-auto text-[10px] px-1.5 py-0.5 rounded bg-purple-600 text-white font-bold">SA</span>
            @endif
          </a>
        </li>

        <li class="pt-3">
          <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">CMS</p>
        </li>
       <li>
          <a href="{{ route('admin.home-sections.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.home-sections.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-house-chimney-window w-5 text-gray-300"></i><span>Home Content</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.notices.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800">
            <i class="fa-solid fa-bell w-5 text-gray-300"></i><span>Notices</span>
          </a>
        </li>
          <li>
          <a href="{{ route('admin.popups.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.popups.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-window-restore w-5 text-gray-300"></i><span>Popups</span>
          </a>
        </li>
        <li>
          <a href="{{ route('admin.gallery.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.gallery.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-images w-5 text-gray-300"></i><span>Gallery</span>
          </a>
        </li>
      
     
        <li>
          <a href="{{ route('admin.calendar-years.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.calendar-years.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-calendar-week w-5 text-gray-300"></i><span>Calendar Years</span>
          </a>
        </li>
        
        <li>
          <a href="{{ route('admin.committees.index') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-800 {{ request()->routeIs('admin.committees.*') ? 'bg-gray-800' : '' }}">
            <i class="fa-solid fa-people-group w-5 text-gray-300"></i><span>Committees</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="pt-16 lg:ml-64 min-h-screen px-4 md:px-6 relative {{ ($siteExpired ?? false) && !auth()->user()?->isSuperAdmin() ? 'blur-sm pointer-events-none select-none' : '' }}">
    @if (session('success'))
    <div class="bg-green-100 text-green-800 px-4 py-2 rounded-lg border border-green-200 my-4">{{ session('success') }}
    </div>
    @endif
    @if ($errors->any())
    <div class="bg-rose-100 text-rose-800 px-4 py-2 rounded-lg border border-rose-200 my-4">
      <ul class="list-disc ml-5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
    @endif

    @yield('content')
  </main>

  @if(($siteExpired ?? false) && !auth()->user()?->isSuperAdmin())
    <div class="fixed inset-0 z-[70] bg-white/70 backdrop-blur-sm flex items-center justify-center p-4">
      <div class="max-w-md rounded-2xl border border-red-200 bg-white p-6 text-center shadow-2xl" style="animation:renewPulse 1.8s ease-in-out infinite">
        <div class="mx-auto mb-3 grid h-14 w-14 place-items-center rounded-full bg-red-100 text-red-600">
          <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
        </div>
        <h2 class="text-xl font-extrabold text-gray-900">Website renewal expired</h2>
        <p class="mt-2 text-sm text-gray-600">Please contact super admin to renew the website as soon as possible.</p>
        <form method="POST" action="{{ route('admin.logout') }}" class="mt-5">
          @csrf
          <button class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm font-semibold">Logout</button>
        </form>
      </div>
    </div>
  @endif

  <script>
    // Profile dropdown
    const profileBtn = document.getElementById('profileDropdownBtn');
    const profileMenu = document.getElementById('profileDropdownMenu');
    if (profileBtn && profileMenu) {
      profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileMenu.classList.toggle('hidden');
      });
      document.addEventListener('click', () => profileMenu.classList.add('hidden'));
    }
    const bellBtn = document.getElementById('adminBellBtn');
    const bellMenu = document.getElementById('adminBellMenu');
    if (bellBtn && bellMenu) {
      bellBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        bellMenu.classList.toggle('hidden');
        profileMenu?.classList.add('hidden');
      });
      document.addEventListener('click', () => bellMenu.classList.add('hidden'));
    }
    // Sidebar toggle
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const backdrop = document.getElementById('sidebarBackdrop');
    function openSidebar() { sidebar.classList.remove('-translate-x-full'); backdrop.classList.remove('hidden'); }
    function closeSidebar() { sidebar.classList.add('-translate-x-full'); backdrop.classList.add('hidden'); }
    if (sidebarToggle && sidebar) sidebarToggle.addEventListener('click', openSidebar);
    if (backdrop) backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSidebar(); });
  </script>
</body>

</html>
