@extends('admin.layout')
@section('title','Admin • Add Person')
@section('content')
<div class="py-6 max-w-5xl mx-auto">

  {{-- Breadcrumb --}}
  <nav class="text-xs text-slate-400 mb-4 flex items-center gap-1">
    <a href="{{ route('admin.persons.index') }}" class="hover:text-slate-600">Members</a>
    <span>/</span>
    <span class="text-slate-600 font-medium">Add Person</span>
  </nav>

  <div class="flex items-center justify-between mb-5">
    <h1 class="text-2xl font-bold text-slate-800">Add New Person</h1>
    <a href="{{ route('admin.persons.index') }}"
      class="px-4 py-2 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">← Back</a>
  </div>

  @if($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
      <strong>Please fix the following errors:</strong>
      <ul class="mt-1 ml-4 list-disc text-xs">
        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.persons.store') }}" enctype="multipart/form-data"
    class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
    @csrf
    @include('admin.persons.form')
    <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
      <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium text-sm shadow-sm">
        💾 Save Person
      </button>
      <a href="{{ route('admin.persons.index') }}"
        class="px-4 py-2 rounded-xl border text-sm text-slate-600 hover:bg-slate-50">Cancel</a>
    </div>
  </form>

</div>
@endsection
