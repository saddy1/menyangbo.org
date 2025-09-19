@extends('admin.layout')
@section('title','Admin • Add Person')
@section('content')
<div class="py-6 max-w-3xl">
  <h1 class="text-2xl font-bold mb-4">Add Person</h1>
  <form method="POST" action="{{ route('admin.persons.store') }}" class="bg-white border rounded-xl p-4 space-y-4">
    @csrf
    @include('admin.persons.form')
    <div class="flex items-center gap-2">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
      <a href="{{ route('admin.persons.index') }}" class="px-4 py-2 rounded-lg border">Cancel</a>
    </div>
  </form>
</div>
@endsection
