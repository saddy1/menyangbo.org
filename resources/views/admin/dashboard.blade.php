@extends('admin.layout')
@section('title','Admin • Dashboard')
@section('content')
<div class="py-6">
  <h1 class="text-2xl font-bold mb-4">Overview</h1>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <div class="text-sm text-slate-500">People</div>
      <div class="text-2xl font-bold">{{ $stats['people'] }}</div>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <div class="text-sm text-slate-500">Relationships</div>
      <div class="text-2xl font-bold">{{ $stats['edges'] }}</div>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <div class="text-sm text-slate-500">Unions</div>
      <div class="text-2xl font-bold">{{ $stats['unions'] }}</div>
    </div>
    <div class="p-4 bg-white rounded-xl border shadow-sm">
      <div class="text-sm text-slate-500">Deceased</div>
      <div class="text-2xl font-bold">{{ $stats['deceased'] }}</div>
    </div>
  </div>

  <div class="mt-8">
    <a href="{{ route('admin.persons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      <i class="fa fa-plus"></i> Add Person
    </a>
  </div>
</div>
@endsection
