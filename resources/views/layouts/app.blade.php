<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','परिवार वृक्ष')</title>

    {{-- Tailwind CDN for quick start --}}
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Alpine.js for drawer/modal --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    

    <style>
        /* small helpers */
        .node-card { @apply bg-white rounded-xl shadow border hover:shadow-lg transition cursor-pointer; }
        .connector { stroke: #cbd5e1; stroke-width: 2; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">

<header class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <span class="text-xl font-bold text-emerald-600">परिवार वृक्ष</span>
      <span class="text-sm text-slate-500">Family Tree</span>
    </div>
    <nav class="hidden md:flex items-center gap-6 text-sm">
      <a href="{{ route('tree.index') }}" class="hover:text-emerald-600">मुख्य पृष्ठ</a>
      <a href="#" class="hover:text-emerald-600">सहायता</a>
    </nav>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 py-6">
  @yield('content')
</main>

<footer class="mt-10 border-t">
  <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-slate-500 flex flex-col md:flex-row items-center justify-between gap-2">
    <div>© {{ date('Y') }} परिवार वृक्ष</div>
    <div>तयार पारिएको: Laravel + Tailwind</div>
  </div>
</footer>

</body>
</html>
