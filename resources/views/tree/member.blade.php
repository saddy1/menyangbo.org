@extends('layouts.app')
@section('title', $person->display_name . ' • प्रोफाइल')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

  {{-- Header --}}
  <div class="flex items-start justify-between gap-3 flex-wrap">
    <div class="min-w-0">
      <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $person->display_name }}</h1>

      <div class="text-sm text-slate-600 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
        <span>ID: <span class="font-semibold text-slate-900">{{ $person->id }}</span></span>
        <span class="text-slate-300">•</span>
        <span>Pusta: <span class="font-semibold text-slate-900">{{ $person->pusta ?? '—' }}</span></span>
        <span class="text-slate-300">•</span>
        <span>Gender: <span class="font-semibold text-slate-900">{{ $person->gender ?? 'unknown' }}</span></span>

        @if($person->is_deceased)
          <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold">
            Deceased
          </span>
        @endif
      </div>
    </div>

    <div class="flex gap-2 shrink-0">
      <a href="{{ route('tree.index', ['root_id' => $person->id]) }}"
         class="rounded-xl border px-3 py-2 text-sm hover:bg-slate-50">
        Open in Tree
      </a>
      <a href="{{ url()->previous() }}"
         class="rounded-xl bg-slate-900 text-white px-3 py-2 text-sm hover:bg-slate-800">
        Back
      </a>
    </div>
  </div>

  {{-- Top Section --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mt-6">

    {{-- Profile --}}
    <div class="lg:col-span-2 rounded-2xl border bg-white p-4 sm:p-5">
      <div class="flex items-center justify-between gap-2 mb-4">
        <div class="text-base font-bold">सदस्य प्रोफाइल</div>

        @if($person->is_deceased)
          <span class="text-xs font-semibold text-red-700 bg-red-50 px-2 py-1 rounded-full">
            मृत्यु विवरण सहित
          </span>
        @endif
      </div>

      {{-- Info grid --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div><span class="text-slate-500">सदस्य न.:</span> <span class="font-semibold">{{ $person->member_no ?? '-' }}</span></div>
        <div><span class="text-slate-500">सदस्यको प्रकार:</span> <span class="font-semibold">{{ $person->member_type ?? '-' }}</span></div>

        <div><span class="text-slate-500">नेपाली नाम:</span> <span class="font-semibold">{{ $person->display_name_np ?? '-' }}</span></div>
        <div><span class="text-slate-500">सदस्यता:</span> <span class="font-semibold">{{ $person->membership ?? '-' }}</span></div>

        <div><span class="text-slate-500">जन्म मिति (A.D.):</span> <span class="font-semibold">{{ $person->birth_date ? $person->birth_date->format('Y-m-d') : '-' }}</span></div>
        <div><span class="text-slate-500">जन्मस्थान:</span> <span class="font-semibold">{{ $person->birth_place ?? '-' }}</span></div>

        {{-- Father / Mother from relationships --}}
        <div>
          <span class="text-slate-500">बुबाको नाम:</span>
          @if(!empty($father))
            <a class="font-semibold hover:underline" href="{{ route('member.page', $father->id) }}">
              {{ $father->display_name }}
            </a>
          @else
            <span class="font-semibold">-</span>
          @endif
        </div>

        <div>
          <span class="text-slate-500">आमाको नाम:</span>
          @if(!empty($mother))
            <a class="font-semibold hover:underline" href="{{ route('member.page', $mother->id) }}">
              {{ $mother->display_name }}
            </a>
          @else
            <span class="font-semibold">-</span>
          @endif
        </div>

        <div><span class="text-slate-500">घरको ठेगाना:</span> <span class="font-semibold">{{ $person->address ?? '-' }}</span></div>
        <div><span class="text-slate-500">मोबाइल नम्बर:</span> <span class="font-semibold">{{ $person->mobile ?? '-' }}</span></div>

        <div><span class="text-slate-500">Email ID:</span> <span class="font-semibold break-all">{{ $person->email ?? '-' }}</span></div>
        <div><span class="text-slate-500">शिक्षा:</span> <span class="font-semibold">{{ $person->education ?? '-' }}</span></div>

        <div><span class="text-slate-500">पेशा:</span> <span class="font-semibold">{{ $person->occupation ?? '-' }}</span></div>
        <div><span class="text-slate-500">Registered By:</span> <span class="font-semibold">{{ $person->registered_by ?? '-' }}</span></div>

        <div><span class="text-slate-500">Updated Date:</span> <span class="font-semibold">{{ $person->updated_at?->format('Y-m-d') ?? '-' }}</span></div>
      </div>

      {{-- ✅ Death details: ONLY if deceased --}}
      @if($person->is_deceased)
        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50/40 p-4">
          <div class="flex items-center justify-between">
            <div class="font-bold text-slate-900">मृत्यु विवरण</div>
            <span class="text-xs font-semibold text-red-700 bg-white border border-red-200 px-2 py-1 rounded-full">
              Deceased
            </span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm mt-3">
            <div>
              <span class="text-slate-500">मृत्यु मिति (A.D.):</span>
              <span class="font-semibold">{{ $person->death_date ? $person->death_date->format('Y-m-d') : '-' }}</span>
            </div>

            <div>
              <span class="text-slate-500">मृत्यु स्थान:</span>
              <span class="font-semibold">{{ $person->death_place ?? '-' }}</span>
            </div>

            <div>
              <span class="text-slate-500">मृत्यु तिथी:</span>
              <span class="font-semibold">{{ $person->death_tithi ?? '-' }}</span>
            </div>

            <div class="sm:col-span-2">
              <span class="text-slate-500">मृत्युको कारण:</span>
              <span class="font-semibold">{{ $person->death_reason ?? '-' }}</span>
            </div>
          </div>
        </div>
      @endif
    </div>

    {{-- Relations --}}
    <div class="rounded-2xl border bg-white p-4 sm:p-5">
      <div class="text-base font-bold mb-3">Relations</div>

      <div class="text-sm space-y-4">

        {{-- Parents --}}
        <div>
          <div class="text-slate-500 text-xs font-semibold mb-2">Parents</div>
          @forelse($person->parents as $pp)
            <a class="block px-3 py-2 rounded-xl border hover:bg-slate-50 mb-2"
               href="{{ route('member.page', $pp->id) }}">
              <div class="flex items-center justify-between">
                <span class="font-semibold truncate">{{ $pp->display_name }}</span>
                <span class="text-xs text-slate-500">#{{ $pp->id }}</span>
              </div>
            </a>
          @empty
            <div class="text-slate-500">—</div>
          @endforelse
        </div>

        {{-- Spouses --}}
        <div>
          @php
            $label = 'Spouses';
            if(($person->gender ?? '') === 'male') $label = 'Wife/Wives';
            elseif(($person->gender ?? '') === 'female') $label = 'Husband(s)';
          @endphp
          <div class="text-slate-500 text-xs font-semibold mb-2">{{ $label }}</div>

          @forelse($spouses as $s)
            <a class="block px-3 py-2 rounded-xl border hover:bg-slate-50 mb-2"
               href="{{ route('member.page', $s->id) }}">
              <div class="flex items-center justify-between">
                <span class="font-semibold truncate">{{ $s->display_name }}</span>
                <span class="text-xs text-slate-500">#{{ $s->id }}</span>
              </div>
            </a>
          @empty
            <div class="text-slate-500">—</div>
          @endforelse
        </div>

        {{-- Children --}}
        <div>
          <div class="text-slate-500 text-xs font-semibold mb-2">
            Children ({{ $person->children->count() }})
          </div>

          @forelse($person->children as $cc)
            <a class="block px-3 py-2 rounded-xl border hover:bg-slate-50 mb-2"
               href="{{ route('member.page', $cc->id) }}">
              <div class="flex items-center justify-between">
                <span class="font-semibold truncate">{{ $cc->display_name }}</span>
                <span class="text-xs text-slate-500">#{{ $cc->id }}</span>
              </div>
            </a>
          @empty
            <div class="text-slate-500">—</div>
          @endforelse
        </div>

      </div>
    </div>

  </div>

  {{-- Bio --}}
  <div class="rounded-2xl border bg-white p-4 sm:p-5 mt-4">
    <div class="text-base font-bold mb-2">जीवनी</div>
    <div class="text-sm text-slate-700 whitespace-pre-line">
      {{ $person->bio ?: '—' }}
    </div>
  </div>

</div>
@endsection
