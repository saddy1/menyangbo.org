@extends('admin.layout')
@section('title', 'Home Page Content')

@section('content')
<div class="py-6 max-w-5xl mx-auto">

  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
      <h1 class="text-xl font-bold text-gray-900">Home Page Content</h1>
      <p class="text-sm text-gray-500 mt-0.5">Edit all static sections shown on the public home page.</p>
    </div>
    <a href="{{ route('home') }}" target="_blank"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
      <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> View Home Page
    </a>
  </div>

  @php
    $sectionMeta = \App\Models\HomeSection::$sections;
    $sectionIcons = [
      'banner'      => 'fa-rectangle-ad',
      'at_a_glance' => 'fa-table-cells-large',
      'timeline'    => 'fa-timeline',
      'key_figures' => 'fa-people-group',
      'notes'       => 'fa-note-sticky',
    ];
    $sectionHelp = [
      'banner'      => 'home page promotional banner. title = headline, subtitle = small label, body = short text, image/link optional.',
      'at_a_glance' => 'title = label, body = value. These show as key-value cards.',
      'timeline'    => 'title = event heading, subtitle = date/badge, body = bullet points (one per line).',
      'key_figures' => 'title = person name, subtitle = role/tag, body = description, color = card accent.',
      'notes'       => 'title = section heading (e.g. "प्रसार"), body = bullet points (one per line).',
    ];
  @endphp

  @foreach($sectionMeta as $key => $label)
  @php $items = $grouped->get($key, collect()); @endphp
  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-6 overflow-hidden">

    {{-- Section header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50/70">
      <div class="flex items-center gap-3">
        <span class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
          <i class="fa-solid {{ $sectionIcons[$key] ?? 'fa-list' }} text-sm"></i>
        </span>
        <div>
          <h2 class="font-semibold text-gray-800 text-sm">{{ $label }}</h2>
          <p class="text-[11px] text-gray-400 mt-0.5">{{ $sectionHelp[$key] ?? '' }}</p>
        </div>
      </div>
      <a href="{{ route('admin.home-sections.create', ['section' => $key]) }}"
         class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition shrink-0">
        <i class="fa-solid fa-plus text-[10px]"></i> Add
      </a>
    </div>

    @if($items->isEmpty())
    <div class="px-5 py-6 text-sm text-gray-400 text-center">
      No items yet. <a href="{{ route('admin.home-sections.create', ['section' => $key]) }}" class="text-blue-600 font-medium hover:underline">Add one</a>.
    </div>
    @else
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-xs text-gray-400 uppercase tracking-wide border-b border-gray-100">
          <th class="px-5 py-2.5 font-medium">Title</th>
          <th class="px-3 py-2.5 font-medium hidden sm:table-cell">Subtitle / Badge</th>
          <th class="px-3 py-2.5 font-medium hidden md:table-cell">Body preview</th>
          <th class="px-3 py-2.5 font-medium text-center w-16">Order</th>
          <th class="px-3 py-2.5 font-medium text-center w-16">Active</th>
          <th class="px-4 py-2.5 font-medium text-right w-24">Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
        <tr class="border-b border-gray-50 hover:bg-gray-50/50 align-top">
          <td class="px-5 py-3">
            <div class="flex items-center gap-2">
              @if($item->image_path)
                <img src="{{ asset($item->image_path) }}" alt="" class="h-8 w-12 rounded-lg object-cover border border-gray-100">
              @endif
              <span class="font-medium text-gray-800 text-sm">{{ $item->title }}</span>
            </div>
            @if($item->color)
            <span class="ml-1 inline-block text-[10px] px-1.5 py-0.5 rounded-full bg-{{ $item->color }}-50 text-{{ $item->color }}-700 ring-1 ring-{{ $item->color }}-200">
              {{ $item->color }}
            </span>
            @endif
          </td>
          <td class="px-3 py-3 text-gray-500 text-xs hidden sm:table-cell">
            {{ $item->subtitle ? Str::limit($item->subtitle, 50) : '—' }}
          </td>
          <td class="px-3 py-3 text-gray-400 text-xs hidden md:table-cell max-w-xs">
            @if($item->body)
              <span class="line-clamp-2">{{ Str::limit($item->body, 80) }}</span>
            @else —
            @endif
          </td>
          <td class="px-3 py-3 text-center text-gray-500 text-xs">{{ $item->sort_order }}</td>
          <td class="px-3 py-3 text-center">
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
              {{ $item->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">
              {{ $item->is_active ? 'ON' : 'OFF' }}
            </span>
          </td>
          <td class="px-4 py-3 text-right">
            <div class="flex items-center justify-end gap-1.5">
              <a href="{{ route('admin.home-sections.edit', $item) }}"
                 class="px-2.5 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                Edit
              </a>
              <form method="POST" action="{{ route('admin.home-sections.destroy', $item) }}"
                    onsubmit="return confirm('Delete this item?')">
                @csrf @method('DELETE')
                <button class="px-2.5 py-1.5 text-xs font-medium text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 transition">
                  Del
                </button>
              </form>
            </div>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
    @endif

  </div>
  @endforeach

</div>
@endsection
