@extends('admin.layout')
@section('title','Admin • Edit Person')
@section('content')
<div class="py-6 max-w-5xl mx-auto">

  {{-- Breadcrumb --}}
  <nav class="text-xs text-slate-400 mb-4 flex items-center gap-1">
    <a href="{{ route('admin.persons.index') }}" class="hover:text-slate-600">Members</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Edit: {{ $person->display_name }}</span>
  </nav>

  <div class="flex items-center justify-between mb-5">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Edit Person</h1>
      <p class="text-xs text-slate-400 mt-0.5">
        ID #{{ $person->id }}
        @if($person->member_no) · <span class="font-mono">{{ $person->member_no }}</span>@endif
        @if($person->pusta) · पुस्ता {{ $person->pusta }}@endif
      </p>
    </div>
    <div class="flex items-center gap-2">
      <a href="{{ route('person.show', $person) }}" target="_blank"
        class="px-3 py-2 rounded-xl border text-xs text-slate-600 hover:bg-slate-50">
        👁 View Profile
      </a>
      <a href="{{ route('admin.persons.index') }}"
        class="px-4 py-2 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">← Back</a>
    </div>
  </div>

  @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-1 ml-4 list-disc text-xs">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
      </ul>
    </div>
  @endif

  {{-- Existing unions panel --}}
  @php $unions = $person->all_unions; @endphp
  @if($unions->count())
    <div class="mb-5 bg-pink-50/60 border border-pink-200 rounded-xl p-4">
      <div class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">💍 Existing Unions</div>
      <div class="flex flex-wrap gap-2">
        @foreach($unions as $u)
          @php
            $spouse = $u->spouse1_id === $person->id ? $u->spouse2 : $u->spouse1;
          @endphp
          @if($spouse)
            <div class="flex items-center gap-2 bg-white border border-pink-200 rounded-lg px-3 py-2 text-sm">
              <span>{{ $spouse->display_name }}</span>
              @if($spouse->display_name_np)<span class="text-slate-400 text-xs">/ {{ $spouse->display_name_np }}</span>@endif
              @if($u->start_date)<span class="text-xs text-slate-400">({{ $u->start_date->format('Y') }})</span>@endif
              <span class="text-[10px] bg-pink-100 text-pink-600 px-1.5 py-0.5 rounded">{{ $u->type ?? 'married' }}</span>
            </div>
          @endif
        @endforeach
      </div>
      <p class="text-[11px] text-slate-400 mt-2">Add another union below, or manage all unions from the <a href="{{ route('admin.unions.index') }}" class="text-indigo-500 hover:underline">Unions</a> page.</p>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.persons.update', $person) }}" enctype="multipart/form-data"
    class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
    @csrf @method('PUT')
    @include('admin.persons.form', ['person' => $person])
    <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
      <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium text-sm shadow-sm">
        💾 Update Person
      </button>
      <a href="{{ route('admin.persons.index') }}"
        class="px-4 py-2 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
    </div>
  </form>

</div>
@endsection
