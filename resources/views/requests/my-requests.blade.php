@extends('layouts.app')
@section('title', \App\Support\FrontendLocale::text('मेरा अनुरोधहरू'))

@section('content')
<div class="max-w-3xl mx-auto py-8">

  <div class="flex items-center justify-between gap-3 mb-6 flex-wrap">
    <div>
      <h1 class="text-2xl font-bold text-gray-900">{{ \App\Support\FrontendLocale::text('मेरा अनुरोधहरू') }}</h1>
      <p class="text-sm text-gray-500 mt-0.5">{{ \App\Support\FrontendLocale::text('तपाईंले पठाएका सबै परिवर्तन अनुरोधहरू') }}</p>
    </div>
    <a href="{{ \App\Support\FrontendLocale::route('home') }}"
      class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
      {{ \App\Support\FrontendLocale::text('← गृहपृष्ठ') }}
    </a>
  </div>

  @if($requests->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-12 text-center">
      <div class="text-5xl mb-4">📋</div>
      <div class="text-gray-500 font-medium">{{ \App\Support\FrontendLocale::text('तपाईंले अहिलेसम्म कुनै अनुरोध पठाउनुभएको छैन।') }}</div>
      <p class="text-sm text-gray-400 mt-1">{{ \App\Support\FrontendLocale::text('कुनै सदस्यको प्रोफाइल खोल्नुहोस् र अनुरोध पठाउनुहोस्।') }}</p>
    </div>
  @else

    {{-- Summary pills --}}
    @php
      $pending  = $requests->getCollection()->where('status', 'pending')->count();
      $approved = $requests->getCollection()->where('status', 'approved')->count();
      $rejected = $requests->getCollection()->where('status', 'rejected')->count();
    @endphp
    <div class="flex gap-3 mb-5 flex-wrap">
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
        ⏳ {{ $pending }} {{ \App\Support\FrontendLocale::text('Pending') }}
      </span>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
        ✓ {{ $approved }} {{ \App\Support\FrontendLocale::text('Approved') }}
      </span>
      <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
        ✗ {{ $rejected }} {{ \App\Support\FrontendLocale::text('Rejected') }}
      </span>
    </div>

    <div class="space-y-3">
      @foreach($requests as $req)
      @php
        $statusCls = match($req->status) {
          'approved' => 'bg-green-100 text-green-700 border-green-200',
          'rejected' => 'bg-red-100 text-red-700 border-red-200',
          default    => 'bg-amber-100 text-amber-700 border-amber-200',
        };
        $statusIcon = match($req->status) {
          'approved' => '✓',
          'rejected' => '✗',
          default    => '⏳',
        };
        $typeIcons = [
          'add_child'      => '👶',
          'mark_deceased'  => '🕊',
          'update_profile' => '✏️',
          'add_union'      => '💍',
          'not_listed'     => '📋',
        ];
        $typeLabels = [
          'add_child'      => \App\Support\FrontendLocale::text('नयाँ सन्तान'),
          'mark_deceased'  => \App\Support\FrontendLocale::text('मृत्यु सूचना'),
          'update_profile' => \App\Support\FrontendLocale::text('प्रोफाइल सम्पादन'),
          'add_union'      => \App\Support\FrontendLocale::text('विवाह जानकारी'),
          'not_listed'     => \App\Support\FrontendLocale::text('सूचीमा नभएको'),
        ];
      @endphp
      <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden
        {{ $req->status === 'pending' ? 'border-l-4 border-l-amber-400' : ($req->status === 'approved' ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-red-400') }}">
        <div class="px-5 py-4">
          <div class="flex items-start justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-3">
              <span class="text-2xl">{{ $typeIcons[$req->type] ?? '📄' }}</span>
              <div>
                <div class="font-semibold text-gray-900 text-sm">
                  {{ $typeLabels[$req->type] ?? ucfirst(str_replace('_', ' ', $req->type)) }}
                  @if($req->person)
                    <span class="text-gray-400 font-normal">— {{ $req->person->display_name }}</span>
                  @endif
                </div>
                <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-2 flex-wrap">
                  <span>Reference: #{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</span>
                  <span>·</span>
                  <span>{{ $req->created_at->format('d M Y, h:i A') }}</span>
                  <span>·</span>
                  <span>{{ $req->created_at->diffForHumans() }}</span>
                </div>
              </div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusCls }} whitespace-nowrap">
              {{ $statusIcon }} {{ ucfirst($req->status) }}
            </span>
          </div>

          {{-- Key payload preview --}}
          @php
            $preview = collect((array)$req->payload)
              ->filter(fn($v) => !empty($v))
              ->take(3);
          @endphp
          @if($preview->isNotEmpty())
          <div class="mt-3 flex flex-wrap gap-2">
            @foreach($preview as $k => $v)
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-50 border border-gray-100 text-xs text-gray-600">
                <span class="text-gray-400">{{ ucfirst(str_replace('_', ' ', $k)) }}:</span>
                {{ is_array($v) ? implode(', ', $v) : Str::limit((string)$v, 40) }}
              </span>
            @endforeach
          </div>
          @endif

          {{-- Review info --}}
          @if($req->status !== 'pending')
          <div class="mt-3 pt-3 border-t border-gray-100 flex items-start gap-2 flex-wrap text-xs text-gray-500">
            @if($req->status === 'approved')
              <span class="text-green-600 font-semibold">{{ \App\Support\FrontendLocale::text('✓ Approved') }}</span>
            @else
              <span class="text-red-600 font-semibold">{{ \App\Support\FrontendLocale::text('✗ Rejected') }}</span>
            @endif
            @if($req->reviewed_at)
              <span>on {{ $req->reviewed_at->format('d M Y') }}</span>
            @endif
            @if($req->review_note)
              <span>·</span>
              <span class="italic text-gray-500">{{ $req->review_note }}</span>
            @endif
          </div>
          @endif
        </div>
      </div>
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
      <div class="mt-6">{{ $requests->links() }}</div>
    @endif

  @endif
</div>
@endsection
