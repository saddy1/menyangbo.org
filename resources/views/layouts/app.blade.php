<!DOCTYPE html>
<html lang="{{ \App\Support\FrontendLocale::locale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.seo')

    @vite('resources/css/app.css')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .site-brand { max-width: min(35vw, 330px); }
        @media (min-width: 1280px) {
            .site-brand { max-width: 240px; }
            .site-desktop-nav > a,
            .site-desktop-nav > div > button { padding-left: 8px; padding-right: 8px; white-space: nowrap; }
        }
        .language-switcher {
            display: inline-flex; align-items: center; gap: 2px; padding: 3px;
            border: 1px solid rgba(255,255,255,.22); border-radius: 999px;
            background: rgba(15,23,42,.18); box-shadow: inset 0 1px 3px rgba(15,23,42,.12);
        }
        .language-option {
            display: inline-flex; align-items: center; justify-content: center; gap: 4px;
            min-width: 44px; min-height: 32px; padding: 4px 7px; border-radius: 999px;
            color: rgba(255,255,255,.8); white-space: nowrap; text-decoration: none;
            transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
        }
        .language-flag { font-size: 17px; line-height: 1; font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif; }
        .language-label { font-size: 10px; line-height: 1; font-weight: 800; letter-spacing: .02em; }
        .language-option:hover { background: rgba(255,255,255,.13); color: white; }
        .language-option[aria-current="true"] {
            background: #fff; color: #1d4ed8; box-shadow: 0 1px 5px rgba(15,23,42,.2);
        }
        .language-option:focus-visible { outline: 2px solid #fbbf24; outline-offset: 3px; }
        @media (prefers-reduced-motion: reduce) { .language-option { transition: none; } }
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        @keyframes marquee { from { transform: translateX(100vw); } to { transform: translateX(-100%); } }
        .animate-marquee { animation: marquee 60s linear infinite; }
        .animate-marquee:hover { animation-play-state: paused; }
        .footer-link { color: rgb(191 219 254); transition: color .18s ease, transform .18s ease; }
        .footer-link:hover { color: white; transform: translateX(3px); }
    </style>
</head>

<body class="public-site bg-slate-50 text-slate-900 antialiased overflow-x-hidden" x-data="{ mobileNav: false }">

    <!-- ═══ HEADER ═══ -->
    <header class="site-header sticky top-0 z-[60] bg-blue-700 text-white backdrop-blur shadow-md">
        <div class="max-w-7xl mx-auto px-4 min-h-16 flex flex-nowrap lg:flex-wrap items-center justify-between gap-y-1 py-1 xl:flex-nowrap xl:py-0">

            <!-- Logo + Title -->
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ \App\Support\FrontendLocale::route('home') }}" class="shrink-0">
                    <img src="{{ asset('menyanbo_logo.png') }}" alt="{{ \App\Support\FrontendLocale::text('मेन्याङ्बो लोगो') }}"
                        class="w-12 h-12 sm:w-14 sm:h-14 object-contain bg-white rounded-full p-1 shadow" />
                </a>
                <div class="min-w-0 flex flex-col site-brand">
                    <a href="{{ \App\Support\FrontendLocale::route('home') }}" class="truncate font-extrabold text-white text-lg sm:text-xl hover:text-red-300">
                        {{ \App\Support\FrontendLocale::text('मेन्याङ्बो कल्याणकारी संघ') }}
                    </a>
                    <span class="hidden sm:inline text-sm text-blue-200">{{ \App\Support\FrontendLocale::text('वंशावली') }}</span>
                </div>
            </div>

            <div class="ml-auto mr-2 shrink-0 lg:hidden">@include('partials.language-switcher')</div>

            <!-- Desktop nav — dynamic menu -->
            <nav class="site-desktop-nav hidden lg:flex order-last w-full flex-wrap items-center justify-center gap-1 pb-2 text-sm font-medium xl:order-none xl:ml-auto xl:w-auto xl:flex-nowrap xl:pb-0">
                @php $menuItems = \App\Models\Menu::activeTopLevel(); @endphp
                @forelse($menuItems as $item)
                    @if($item->children->count())
                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                            <button @click.prevent="open = !open" class="px-3 py-2 rounded hover:bg-blue-800 flex items-center gap-1">
                                {{ \App\Support\FrontendLocale::text($item->label) }}
                                <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-cloak x-show="open" x-transition.origin.top.left @click.outside="open = false"
                                class="absolute left-0 top-full pt-1 w-48 z-[70]">
                                <div class="bg-white text-slate-800 rounded-lg shadow-xl border border-slate-100 overflow-hidden">
                                @foreach($item->children as $child)
                                    <a href="{{ \App\Support\FrontendLocale::url($child->href) }}" target="{{ $child->target }}"
                                        class="block px-4 py-2 text-sm hover:bg-slate-50">
                                        {{ \App\Support\FrontendLocale::text($child->label) }}
                                    </a>
                                @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ \App\Support\FrontendLocale::url($item->href) }}" target="{{ $item->target }}"
                            class="px-3 py-2 rounded hover:bg-blue-800">{{ \App\Support\FrontendLocale::text($item->label) }}</a>
                    @endif
                @empty
                    {{-- Default links when no menus configured --}}
                    <a href="{{ \App\Support\FrontendLocale::route('committee.index') }}" class="px-3 py-2 rounded hover:bg-blue-800">{{ \App\Support\FrontendLocale::text('कार्यसमिति') }}</a>
                    <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">
                        <button @click.prevent="open = !open" class="px-3 py-2 rounded hover:bg-blue-800 flex items-center gap-1">
                            {{ \App\Support\FrontendLocale::text('वंशावली') }}
                            <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition.origin.top.left @click.outside="open = false"
                            class="absolute left-0 top-full pt-1 w-48 z-[70]">
                            <div class="bg-white text-slate-800 rounded-lg shadow-xl border border-slate-100 overflow-hidden">
                                <a href="{{ \App\Support\FrontendLocale::route('tree.index') }}"
                                    class="block px-4 py-2 text-sm hover:bg-slate-50">
                                    {{ \App\Support\FrontendLocale::text('Tree') }}
                                </a>
                                <a href="{{ \App\Support\FrontendLocale::route('admin.people.directory') }}"
                                    class="block px-4 py-2 text-sm hover:bg-slate-50">
                                    {{ \App\Support\FrontendLocale::text('पारिवारिक सूची') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="{{ \App\Support\FrontendLocale::route('feedback.create') }}" class="px-3 py-2 rounded hover:bg-blue-800">{{ \App\Support\FrontendLocale::text('सुझाव') }}</a>
                @endforelse

                <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}"
                    class="px-3 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('notices.*') ? 'bg-blue-800' : '' }}">
                    {{ \App\Support\FrontendLocale::text('सूचना') }}
                </a>
                <a href="{{ \App\Support\FrontendLocale::route('gallery.index') }}"
                    class="px-3 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('gallery.index') ? 'bg-blue-800' : '' }}">
                    {{ \App\Support\FrontendLocale::text('ग्यालेरी') }}
                </a>
                <a href="{{ \App\Support\FrontendLocale::route('calendar.index') }}"
                    class="px-3 py-2 rounded hover:bg-blue-800 {{ request()->routeIs('calendar.index') ? 'bg-blue-800' : '' }}">
                    {{ \App\Support\FrontendLocale::text('पात्रो') }}
                </a>

                <!-- Auth buttons -->
                @auth
                    <div class="relative ml-2" x-data="{ open: false }">
                        <button @click="open=!open" @click.outside="open=false"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-blue-800 transition-colors">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" class="w-7 h-7 rounded-full object-cover ring-2 ring-white/30">
                            @else
                                <span class="w-7 h-7 rounded-full bg-white text-blue-700 flex items-center justify-center font-bold text-xs ring-2 ring-white/30">
                                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </span>
                            @endif
                            <span class="max-w-[90px] truncate text-sm">{{ auth()->user()->name }}</span>
                            <svg class="w-3 h-3 opacity-60 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-cloak x-show="open"
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full mt-1 w-52 z-[70] origin-top-right">
                            <div class="bg-white text-slate-800 rounded-xl shadow-xl border border-slate-100 overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100">
                                    <div class="text-xs font-semibold text-slate-700 truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ \App\Support\FrontendLocale::route('my.requests') }}"
                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    {{ \App\Support\FrontendLocale::text('मेरा अनुरोधहरू') }}
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ \App\Support\FrontendLocale::route('admin.dashboard') }}"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50 font-semibold transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                        {{ \App\Support\FrontendLocale::text('Admin Panel') }}
                                    </a>
                                @endif
                                <form method="POST" action="{{ \App\Support\FrontendLocale::route('logout') }}" class="border-t border-slate-100">
                                    @csrf
                                    <button class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-rose-50 text-rose-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        {{ \App\Support\FrontendLocale::text('लगआउट') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}" class="ml-2 px-3 py-1.5 rounded-lg bg-white text-blue-700 font-semibold text-sm hover:bg-blue-50 transition-colors shadow-sm">{{ \App\Support\FrontendLocale::text('लगइन') }}</a>
                    <a href="{{ \App\Support\FrontendLocale::route('register') }}" class="px-3 py-1.5 rounded-lg border border-white/60 text-white font-semibold text-sm hover:bg-blue-800 transition-colors">{{ \App\Support\FrontendLocale::text('दर्ता') }}</a>
                @endauth
                <div class="ml-2 shrink-0">@include('partials.language-switcher')</div>
            </nav>

            <!-- Mobile button -->
            <button class="lg:hidden shrink-0 w-10 h-10 rounded-lg hover:bg-blue-800 flex items-center justify-center"
                @click="mobileNav=true" aria-label="{{ \App\Support\FrontendLocale::text('Open menu') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    <!-- ═══ NOTICE BAR ═══ -->
    @php $marqueeNotices = \App\Models\Notice::forMarquee(); @endphp
    @if($marqueeNotices->count() || isset($calendarToday))
    <div class="site-notice overflow-hidden" style="background:#173c35;">
        <div class="flex items-stretch">
            {{-- "सूचना" pill --}}
            <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}"
               class="shrink-0 flex items-center gap-2 px-4 py-2 font-bold text-xs tracking-widest uppercase transition-opacity hover:opacity-80 whitespace-nowrap"
               style="background:#a18a51;color:#fff;">
                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zm0 16a2 2 0 01-2-2h4a2 2 0 01-2 2z"/>
                </svg>
                {{ \App\Support\FrontendLocale::text('सूचना') }}
            </a>

            {{-- Divider --}}
            <span class="w-px bg-white/10 shrink-0"></span>

            {{-- Scrolling ticker --}}
            <div class="overflow-hidden flex-1 relative flex items-center h-10">
                <div class="animate-marquee whitespace-nowrap flex items-center gap-16 px-8">
                    @for($repeat = 0; $repeat < 2; $repeat++)
                        @foreach($marqueeNotices as $notice)
                        <a href="{{ \App\Support\FrontendLocale::route('notices.show', $notice) }}" class="inline-flex items-center gap-2.5 hover:opacity-90">
                            {{-- dot --}}
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            {{-- date --}}
                            <span class="text-amber-300 font-semibold text-[11px] tracking-wide">{{ $notice->display_date_time }}</span>
                            <span class="text-white/30 text-xs">|</span>
                            {{-- title --}}
                            <span class="text-white font-medium text-sm">{{ $notice->title }}</span>
                            {{-- body snippet --}}
                            @if($notice->body)
                            <span class="text-white/50 text-xs">— {{ Str::limit($notice->body, 55) }}</span>
                            @endif
                            <span class="text-amber-400 font-semibold text-xs">{{ \App\Support\FrontendLocale::text('हेर्नुहोस् →') }}</span>
                        </a>
                        @endforeach
                    @endfor
                </div>
                {{-- fade left/right --}}
                <span class="absolute left-0 inset-y-0 w-6 pointer-events-none" style="background:linear-gradient(90deg,#0f172a,transparent)"></span>
                <span class="absolute right-0 inset-y-0 w-6 pointer-events-none" style="background:linear-gradient(270deg,#0f172a,transparent)"></span>
            </div>

            {{-- "All notices" arrow --}}
            <a href="{{ \App\Support\FrontendLocale::route('calendar.index') }}"
               class="shrink-0 hidden sm:flex items-center gap-2 px-3 lg:px-4 text-white/80 hover:text-white transition-colors border-l border-white/10 bg-white/5">
                <span class="grid h-7 w-7 place-items-center rounded-lg bg-amber-400 text-slate-950 font-black text-xs">
                    {{ \App\Support\FrontendLocale::number($calendarToday['bs']['day'] ?? '') }}
                </span>
                <span class="leading-tight text-xs">
    <span class="block font-bold">{{ \App\Support\FrontendLocale::dateLabel($calendarToday['bs']['label'] ?? '') }}</span>

    <span class="block text-white/50 flex items-center gap-2">
        {{ $calendarToday['ad']['label'] ?? '' }}

        <!-- ✅ Styled Clock -->
        <span id="clock"
              class="px-2 py-[2px] rounded bg-white/10 text-white text-xs font-semibold tracking-wider">
        </span>
    </span>
</span>
            </a>
        </div>
    </div>
    @endif

    @if($siteExpired ?? false)
    <div class="fixed bottom-4 left-1/2 z-[80] -translate-x-1/2 px-4">
        <div class="rounded-2xl border border-red-200 bg-white/95 px-5 py-3 text-center shadow-2xl backdrop-blur"
             style="animation: publicRenewPulse 1.6s ease-in-out infinite;">
            <div class="text-sm font-extrabold text-red-700">{{ \App\Support\FrontendLocale::text('Renew website as soon as possible') }}</div>
            <div class="text-xs text-slate-500">{{ \App\Support\FrontendLocale::text('सेवा नवीकरण आवश्यक छ। कृपया व्यवस्थापकलाई सम्पर्क गर्नुहोस्।') }}</div>
        </div>
    </div>
    <style>
      @keyframes publicRenewPulse {
        0%,100% { transform: scale(1); box-shadow: 0 18px 45px rgba(220,38,38,.18); }
        50% { transform: scale(1.035); box-shadow: 0 22px 60px rgba(220,38,38,.32); }
      }
    </style>
    @endif

    <!-- ═══ MOBILE DRAWER ═══ -->
    <div class="fixed inset-0 z-[70] lg:hidden" x-show="mobileNav" x-transition.opacity style="display:none">
        <div class="absolute inset-0 bg-black/40" @click="mobileNav=false"></div>
        <aside class="absolute top-0 right-0 h-full w-72 max-w-[85%] bg-white shadow-xl border-l
             transform transition-transform duration-300 ease-out"
            x-show="mobileNav"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            @keydown.escape.window="mobileNav=false">

            <div class="h-16 px-4 border-b flex items-center justify-between bg-blue-700 text-white">
                <span class="font-semibold">{{ \App\Support\FrontendLocale::text('Menu') }}</span>
                <button class="w-9 h-9 rounded-lg hover:bg-blue-800 flex items-center justify-center"
                    @click="mobileNav=false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <nav class="flex p-4 space-y-1 text-sm font-medium flex-col overflow-y-auto">
                @forelse(\App\Models\Menu::activeTopLevel() as $item)
                    @if($item->children->count())
                        <details class="group">
                            <summary class="cursor-pointer flex items-center justify-between px-3 py-2 rounded hover:bg-slate-100 list-none">
                                {{ \App\Support\FrontendLocale::text($item->label) }}
                                <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </summary>
                            <div class="ml-4 mt-1 space-y-1">
                                @foreach($item->children as $child)
                                    <a href="{{ \App\Support\FrontendLocale::url($child->href) }}" class="block px-3 py-1.5 rounded text-slate-700 hover:bg-slate-100">{{ \App\Support\FrontendLocale::text($child->label) }}</a>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <a href="{{ \App\Support\FrontendLocale::url($item->href) }}" target="{{ $item->target }}"
                            class="block px-3 py-2 rounded hover:bg-slate-100">{{ \App\Support\FrontendLocale::text($item->label) }}</a>
                    @endif
                @empty
                    <a href="{{ \App\Support\FrontendLocale::route('committee.index') }}" class="block px-3 py-2 rounded hover:bg-slate-100">{{ \App\Support\FrontendLocale::text('कार्यसमिति') }}</a>
                    <details class="group">
                        <summary class="cursor-pointer flex items-center justify-between px-3 py-2 rounded hover:bg-slate-100 list-none">
                            {{ \App\Support\FrontendLocale::text('वंशावली') }}
                            <svg class="w-4 h-4 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </summary>
                        <div class="ml-4 mt-1 space-y-1">
                            <a href="{{ \App\Support\FrontendLocale::route('tree.index') }}" class="block px-3 py-1.5 rounded text-slate-700 hover:bg-slate-100">{{ \App\Support\FrontendLocale::text('Tree') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('admin.people.directory') }}" class="block px-3 py-1.5 rounded text-slate-700 hover:bg-slate-100">{{ \App\Support\FrontendLocale::text('पारिवारिक सूची') }}</a>
                        </div>
                    </details>
                    <a href="{{ \App\Support\FrontendLocale::route('feedback.create') }}" class="block px-3 py-2 rounded hover:bg-slate-100">{{ \App\Support\FrontendLocale::text('सुझाव') }}</a>
                @endforelse

                <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}"
                    class="block px-3 py-2 rounded hover:bg-slate-100 {{ request()->routeIs('notices.*') ? 'bg-slate-100 font-semibold text-blue-700' : '' }}">
                    {{ \App\Support\FrontendLocale::text('सूचना') }}
                </a>
                <a href="{{ \App\Support\FrontendLocale::route('gallery.index') }}"
                    class="block px-3 py-2 rounded hover:bg-slate-100 {{ request()->routeIs('gallery.index') ? 'bg-slate-100 font-semibold text-blue-700' : '' }}">
                    {{ \App\Support\FrontendLocale::text('ग्यालेरी') }}
                </a>
                <a href="{{ \App\Support\FrontendLocale::route('calendar.index') }}"
                    class="block px-3 py-2 rounded hover:bg-slate-100 {{ request()->routeIs('calendar.index') ? 'bg-slate-100 font-semibold text-blue-700' : '' }}">
                    {{ \App\Support\FrontendLocale::text('पात्रो') }}
                </a>

                <hr class="my-2">
                @auth
                    <div class="px-3 py-2 flex items-center gap-2">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                            </span>
                        @endif
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <a href="{{ \App\Support\FrontendLocale::route('my.requests') }}"
                        class="block px-3 py-2 rounded text-slate-700 text-sm hover:bg-slate-100">
                        {{ \App\Support\FrontendLocale::text('📋 मेरा अनुरोधहरू') }}
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ \App\Support\FrontendLocale::route('admin.dashboard') }}"
                            class="block px-3 py-2 rounded text-blue-700 font-semibold text-sm hover:bg-blue-50">
                            {{ \App\Support\FrontendLocale::text('⚙ Admin Panel') }}
                        </a>
                    @endif
                    <form method="POST" action="{{ \App\Support\FrontendLocale::route('logout') }}">
                        @csrf
                        <button class="w-full text-left px-3 py-2 rounded hover:bg-rose-50 text-rose-600 text-sm">{{ \App\Support\FrontendLocale::text('लगआउट') }}</button>
                    </form>
                @else
                    <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}" class="block px-3 py-2 rounded bg-blue-600 text-white text-center font-semibold text-sm">{{ \App\Support\FrontendLocale::text('लगइन') }}</a>
                    <a href="{{ \App\Support\FrontendLocale::route('register') }}" class="block px-3 py-2 rounded border border-blue-600 text-blue-600 text-center font-semibold text-sm">{{ \App\Support\FrontendLocale::text('दर्ता') }}</a>
                @endauth
            </nav>
        </aside>
    </div>

    <!-- ═══ FLASH MESSAGES ═══ -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 pt-4">
        <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-700 text-sm">{{ session('success') }}</div>
    </div>
    @endif

    <!-- ═══ MAIN ═══ -->
    <main id="main-content" class="site-main max-w-7xl mx-auto px-4 py-8 {{ request()->routeIs('home') ? 'home-main' : '' }}">
        @yield('content')
    </main>

    <!-- ═══ FOOTER ═══ -->
    <footer class="site-footer mt-14 bg-gradient-to-br from-blue-900 to-blue-950 text-blue-50 border-t border-blue-700/50">
        <div class="relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-blue-300 to-transparent"></div>
            <div class="absolute -right-32 -top-32 h-80 w-80 rounded-full bg-blue-500/15 blur-3xl"></div>
            <div class="absolute -left-32 -bottom-16 h-72 w-72 rounded-full bg-blue-600/15 blur-3xl"></div>

            <div class="relative max-w-7xl mx-auto px-4 py-12">
                <div class="grid gap-10 lg:grid-cols-[1.35fr_1fr_1fr_1fr]">
                    <div>
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('menyanbo_logo.png') }}" alt="{{ \App\Support\FrontendLocale::text('मेन्याङ्बो लोगो') }}"
                                class="h-14 w-14 rounded-full bg-white object-contain p-1.5 shadow-lg ring-2 ring-blue-400/30">
                            <div>
                                <h3 class="text-lg font-extrabold text-white leading-tight">{{ \App\Support\FrontendLocale::text('मेन्याङ्बो कल्याणकारी संघ') }}</h3>
                                <p class="text-sm text-blue-100 mt-0.5">{{ \App\Support\FrontendLocale::text('वंशावली तथा सदस्य अभिलेख प्रणाली') }}</p>
                            </div>
                        </div>
                        <p class="mt-4 max-w-md text-sm leading-7 text-blue-100/75">
                            {{ \App\Support\FrontendLocale::text('लिम्बुवानको ऐतिहासिक मेन्याङ्बो वंशलाई डिजिटल रूपमा सुरक्षित राख्ने, सदस्य विवरण व्यवस्थापन गर्ने र संघका सूचना, ग्यालेरी तथा पात्रो एउटै ठाउँमा उपलब्ध गराउने आधिकारिक पोर्टल।') }}
                        </p>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200 mb-4">{{ \App\Support\FrontendLocale::text('मुख्य लिंकहरू') }}</h4>
                        <nav class="grid gap-3 text-sm">
                            <a href="{{ \App\Support\FrontendLocale::route('home') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('गृहपृष्ठ') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('committee.index') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('कार्यसमिति') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('tree.index') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('वंशावली Tree') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('admin.people.directory') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('पारिवारिक सूची') }}</a>
                        </nav>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200 mb-4">{{ \App\Support\FrontendLocale::text('सुविधाहरू') }}</h4>
                        <nav class="grid gap-3 text-sm">
                            <a href="{{ \App\Support\FrontendLocale::route('notices.public') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('सूचना') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('gallery.index') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('फोटो ग्यालेरी') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('calendar.index') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('पात्रो') }}</a>
                            <a href="{{ \App\Support\FrontendLocale::route('feedback.create') }}" class="footer-link text-blue-50 hover:text-white">{{ \App\Support\FrontendLocale::text('सुझाव पठाउनुहोस्') }}</a>
                        </nav>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-[0.18em] text-blue-200 mb-4">{{ \App\Support\FrontendLocale::text('सम्पर्क') }}</h4>
                        <div class="space-y-3 text-sm text-blue-100">
                            <div class="flex gap-3">
                                <span class="mt-0.5 inline-flex h-7 w-7 items-center justify-center rounded-lg bg-blue-700/50 text-blue-200">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M20 10c0 6-8 11-8 11S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                </span>
                                <span>{{ \App\Support\FrontendLocale::text('धरान–१५, सुनसरी, नेपाल') }}</span>
                            </div>
                            <a href="{{ \App\Support\FrontendLocale::route('feedback.create') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-900/50 transition hover:bg-blue-500 hover:shadow-lg">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4 20-7ZM22 2 11 13"/></svg>
                                {{ \App\Support\FrontendLocale::text('सुझाव / सम्पर्क') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="mt-10 border-t border-blue-700/40 pt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs text-blue-200/70">
                        © {{ date('Y') }} {{ \App\Support\FrontendLocale::text('मेन्याङ्बो कल्याणकारी संघ') }}। {{ \App\Support\FrontendLocale::text('All rights reserved.') }}
                    </p>
                    <p class="text-xs text-blue-200/70">
                        {{ \App\Support\FrontendLocale::text('Created by') }}
                        <a href="https://broadtechinfosys.com.np" target="_blank" rel="noopener"
                            class="font-semibold text-blue-100 hover:text-white transition">
                            Broad Tech Infosys
                        </a>
                        <span class="text-blue-700">•</span>
                        <a href="https://broadtechinfosys.com.np" target="_blank" rel="noopener"
                            class="text-blue-200 hover:text-white transition">broadtechinfosys.com.np</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

<script>
// Marquee continues from where it was across page navigations
(function () {
    var KEY = 'mq_t0', DURATION = 60000;
    var now = Date.now();
    if (!sessionStorage.getItem(KEY)) sessionStorage.setItem(KEY, now);
    var elapsed = (now - parseInt(sessionStorage.getItem(KEY))) % DURATION;
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.querySelector('.animate-marquee');
        if (el) el.style.animationDelay = '-' + (elapsed / 1000).toFixed(2) + 's';
    });
})();
</script>
<script>
function updateClock() {
    const el = document.getElementById("clock");
    if (!el) return;

    const now = new Date();

    let hours = now.getHours();
    let minutes = now.getMinutes();
    let seconds = now.getSeconds();

    // AM / PM
    let ampm = hours >= 12 ? 'PM' : 'AM';

    // Convert to 12-hour format
    hours = hours % 12;
    hours = hours ? hours : 12;

    // Leading zero
    hours = String(hours).padStart(2, '0');
    minutes = String(minutes).padStart(2, '0');
    seconds = String(seconds).padStart(2, '0');

    el.innerText = `${hours}:${minutes}:${seconds} ${ampm}`;
}

updateClock();
setInterval(updateClock, 1000);
</script>
</body>
</html>
