<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title','Portal')</title>
  <link rel="icon" type="image/x-icon" href="{{asset('menyanbo_logo.png')}}">
  @vite('resources/css/app.css')
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

        <!-- Placeholder profile (auth later) -->
        <div class="relative">
          <button id="profileDropdownBtn" class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-100">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-semibold"> {{
              strtoupper(mb_substr($admin->name, 0, 1)) }}</div>
            <span class="hidden md:inline-block font-medium">{{$admin->name}}</span>
            <i class="fa-solid fa-chevron-down text-sm text-gray-500"></i>
          </button>
          <div id="profileDropdownMenu"
            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-50">
            <div class="px-4 py-2 text-sm text-gray-600 border-b">Admin</div>
            <div class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" href="#">
              <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                  Logout
                </button>
              </form>
            </div>
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
            <i class="fas fa-sitemap w-5 text-gray-300"></i><span>Feedback</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="pt-16 lg:ml-64 min-h-screen px-4 md:px-6">
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