@extends('admin.layout')
@section('title','Feedback #'.$feedback->id)

@section('content')
<div class="mx-auto max-w-4xl p-4 sm:p-6">
  {{-- Header / Actions --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
      <div class="text-xs text-slate-500 uppercase tracking-wider">Feedback</div>
      <h1 class="text-2xl font-semibold leading-tight">#{{ $feedback->id }}</h1>
      <div class="mt-1 text-sm text-slate-500">
        Received {{ $feedback->created_at->format('Y-m-d H:i') }}
      </div>
    </div>

    <div class="flex items-center gap-2">
      <a href="{{ route('admin.feedback.index') }}"
         class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
        ← Back
      </a>

      <form method="POST" action="{{ route('admin.feedback.destroy',$feedback) }}"
            onsubmit="return confirm('Delete this feedback?')">
        @csrf @method('DELETE')
        <button
          class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 text-sm">
          🗑️ Delete
        </button>
      </form>
    </div>
  </div>

  {{-- Card --}}
  <div class="rounded-2xl border bg-white shadow-sm overflow-hidden">
    {{-- Top strip --}}
    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-500 via-blue-500 to-fuchsia-500"></div>

    <div class="p-5 sm:p-7">
      {{-- Identity row --}}
      <div class="flex items-start gap-4">
        <div class="shrink-0 w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-lg">
          {{ mb_strtoupper(mb_substr($feedback->name ?? 'G',0,1)) }}
        </div>
        <div class="min-w-0">
          <div class="text-lg font-medium truncate">
            {{ $feedback->name ?: '—' }}
          </div>
          <div class="mt-1 flex flex-wrap gap-2 text-xs">
            @if($feedback->email)
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                ✉️ <a href="mailto:{{ $feedback->email }}" class="hover:underline">{{ $feedback->email }}</a>
              </span>
            @else
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">✉️ —</span>
            @endif

            @if($feedback->contact)
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
                📞 {{ $feedback->contact }}
              </span>
            @else
              <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">📞 —</span>
            @endif
          </div>
        </div>
      </div>

      {{-- Meta grid --}}
      <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="rounded-lg border bg-slate-50/50 p-3">
          <div class="text-xs uppercase text-slate-500">Submitted</div>
          <div class="text-sm font-medium text-slate-800">
            {{ $feedback->created_at->format('Y-m-d H:i') }}
          </div>
        </div>

        <div class="rounded-lg border bg-slate-50/50 p-3">
          <div class="text-xs uppercase text-slate-500">IP / Agent</div>
          <div class="text-xs text-slate-700 break-all">
            {{ $feedback->ip ?: '—' }}
          </div>
          <div class="text-xs text-slate-500 mt-0.5 line-clamp-1">
            {{ $feedback->user_agent ?: '—' }}
          </div>
        </div>
      </div>

      {{-- Description --}}
      <div class="mt-6">
        <div class="mb-2 flex items-center justify-between">
          <h2 class="text-base font-semibold">Description</h2>
          <button
            type="button"
            onclick="navigator.clipboard.writeText(document.getElementById('fb-body-{{ $feedback->id }}').innerText)"
            class="text-xs px-2 py-1 rounded border hover:bg-slate-50">
            Copy
          </button>
        </div>
        <div id="fb-body-{{ $feedback->id }}"
             class="whitespace-pre-wrap rounded-xl border bg-slate-50/60 p-4 text-sm leading-6 text-slate-800">
          {{ $feedback->description }}
        </div>
      </div>
    </div>
  </div>

  {{-- Footer actions (mobile friendly) --}}
  <div class="mt-5 flex flex-col sm:flex-row gap-2 sm:justify-end">
    <a href="mailto:{{ $feedback->email }}" 
       class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm disabled:opacity-50"
       @disabled(empty($feedback->email))>
      Reply via Email
    </a>
    @if($feedback->contact)
      <a href="tel:{{ preg_replace('/\s+/', '', $feedback->contact) }}"
         class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
        Call
      </a>
    @endif
  </div>
</div>
@endsection
