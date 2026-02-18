<!DOCTYPE html>
<html lang="ne">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'परिवार वृक्ष')</title>

    @vite('resources/css/app.css')

    {{-- Alpine.js --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Optional: smooth scrolling for small toolbars */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 antialiased" x-data="{ mobileNav: false }">

    <!-- Header -->
    <header class="sticky top-0 z-40 bg-blue-700 text-white backdrop-blur shadow-md">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">

            <!-- Logo + Title -->
            <div class="flex items-center gap-3 min-w-0">
                <a href="/" class="shrink-0">
                    <img src="{{ asset('menyanbo_logo.png') }}" alt="मेन्याङ्बो लोगो"
                        class="w-12 h-12 sm:w-14 sm:h-14 object-contain bg-white rounded-full p-1 shadow" />
                </a>
                <div class="min-w-0 flex flex-col">
                    <a href="/" class="truncate font-extrabold text-white text-lg sm:text-xl hover:text-red-300">
                        मेन्याङ्बो कल्याणकारी संघ
                    </a>
                    <span class="hidden sm:inline text-sm text-blue-200">Family Tree</span>
                </div>
            </div>

            <!-- Desktop nav -->
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('committee.index') }}" class="hover:text-red-300">कार्यसमिति</a>

                <a href="{{ route('tree.index') }}" class="hover:text-red-300">वंशावली</a>
                                <a href="{{ route('admin.people.directory') }}" class="hover:text-red-300">परिवार सूची</a>

                <a href="{{ route('feedback.create') }}" class="text-sm hover:underline">सुझाव</a>

                <a href="#" class="hover:text-red-300">सहायता</a>
            </nav>

            <!-- Mobile button -->
            <button class="md:hidden w-10 h-10 rounded-lg hover:bg-blue-600 flex items-center justify-center"
                @click="mobileNav=true" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Mobile Right Drawer -->
    <div class="fixed inset-0 z-50 md:hidden" x-show="mobileNav" x-transition.opacity style="display:none">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="mobileNav=false"></div>

        <!-- Drawer -->
        <aside
            class="absolute top-0 right-0 h-full w-72 max-w-[85%] bg-white shadow-xl border-l
             transform transition-transform duration-300 ease-out"
            x-show="mobileNav" x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            @keydown.escape.window="mobileNav=false">

            <!-- Drawer header -->
            <div class="h-16 px-4 border-b flex items-center justify-between">
                <span class="font-semibold">Menu</span>
                <button class="w-9 h-9 rounded-lg hover:bg-slate-100 flex items-center justify-center"
                    @click="mobileNav=false" aria-label="Close menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Drawer nav -->
            <nav class="flex p-4 space-y-2 text-sm font-medium flex-col">
               <a href="{{ route('committee.index') }}" class="hover:text-red-300">कार्यसमिति</a>
                <a href="{{ route('tree.index') }}" class="hover:text-red-300">वंशावली</a>
                <a href="{{ route('feedback.create') }}" class="text-sm hover:underline">सुझाव</a>
            </nav>
        </aside>
    </div>

    <!-- Main -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-12 bg-blue-800 text-blue-100 border-t border-blue-900">
        <div class="max-w-7xl mx-auto px-4 py-8 grid gap-6 md:grid-cols-3 text-sm">

            <!-- Left: Organization -->
            <div>
                <h3 class="text-base font-semibold text-white mb-2">मेन्याङ्बो कल्याणकारी संघ</h3>
                <p class="text-blue-200 leading-relaxed">
                    धरान–१५, सुनसरी, नेपाल<br>
                    परिवार वृक्ष परियोजना
                </p>
            </div>

            <!-- Middle: Links -->
            <div class="flex flex-col space-y-2">
                <a href="{{ route('committee.index') }}" class="hover:text-red-300">कार्यसमिति</a>
                <a href="{{ route('tree.index') }}" class="hover:text-red-300">वंशावली</a>
                <a href="{{ route('feedback.create') }}" class="text-sm hover:underline">सुझाव</a>
            </div>

            <!-- Right: Designer -->
            <div class="text-blue-200 text-sm">
                <p>Designed & Developed by
                    <a href="https://sadanandpaneru.com.np" target="_blank" rel="noopener"
                        class="text-white font-medium hover:text-red-300 transition">
                        Sadanand Paneru
                    </a>
                </p>
                <p class="mt-2">© {{ date('Y') }} मेन्याङ्बो कल्याणकारी संघ</p>
            </div>
        </div>

        <!-- Bottom strip -->

    </footer>

</body>


</html>
