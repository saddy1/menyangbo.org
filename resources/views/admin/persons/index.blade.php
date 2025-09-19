@extends('admin.layout')
@section('title','Admin • Persons')
@section('content')
<div class="py-6">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
    <h1 class="text-2xl font-bold">Persons</h1>
    <a href="{{ route('admin.persons.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      <i class="fa fa-plus mr-1"></i> Add Person
    </a>
  </div>

  <form method="GET" class="flex flex-wrap items-end gap-2 mb-4">
    <div>
      <label class="text-xs text-slate-600">Search</label>
      <input type="text" name="q" value="{{ $q }}" class="border rounded-lg px-3 py-2" placeholder="Name...">
    </div>
    <div>
      <label class="text-xs text-slate-600">Status</label>
      <select name="alive" class="border rounded-lg px-3 py-2">
        <option value="">All</option>
        <option value="1" @selected(request('alive')==='1')>Alive</option>
        <option value="0" @selected(request('alive')==='0')>Deceased</option>
      </select>
    </div>
    <button class="px-4 py-2 bg-slate-800 text-white rounded-lg">Filter</button>
  </form>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($people as $p)
      <div class="p-4 bg-white border rounded-xl shadow-sm">
        <div class="flex items-start gap-3">
          <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center">👤</div>
          <div class="flex-1">
            <div class="font-semibold">{{ $p->display_name }}</div>
            <div class="text-xs text-slate-500">
              Gender: {{ $p->gender }}<br>
              Birth: {{ optional($p->birth_date)->format('Y-m-d') ?? '—' }} |
              Death: {{ $p->is_deceased ? (optional($p->death_date)->format('Y-m-d') ?? '—') : '—' }}
            </div>
          </div>
        </div>
        <div class="flex items-center gap-2 mt-3">
          <a href="{{ route('admin.persons.edit',$p) }}" class="px-3 py-1.5 text-sm rounded-lg border hover:bg-slate-50">Edit</a>
          <form method="POST" action="{{ route('admin.persons.destroy',$p) }}" onsubmit="return confirm('Delete this person?')">
            @csrf @method('DELETE')
            <button class="px-3 py-1.5 text-sm rounded-lg border hover:bg-rose-50 text-rose-700">Delete</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-4">{{ $people->links() }}</div>
</div>
@endsection
