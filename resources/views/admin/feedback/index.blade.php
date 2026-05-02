@extends('admin.layout')
@section('title', 'Feedback')

@section('content')
<div class="mx-auto max-w-7xl p-6">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold text-slate-800">Feedback (Latest)</h1>
  </div>

  @if($rows->isEmpty())
    <div class="p-8 bg-white border border-slate-200 rounded-xl text-center shadow-sm">
      <p class="text-slate-500 text-lg">No feedback yet.</p>
    </div>
  @else
    <div class="overflow-hidden bg-white border border-slate-200 rounded-xl shadow-sm">
      <table class="w-full text-sm text-slate-700">
        <thead class="bg-slate-100 border-b">
          <tr>
            <th class="p-3 text-left font-medium hidden md:table-cell">#</th>
            <th class="p-3 text-left font-medium">Name</th>
            <th class="p-3 text-left font-medium hidden md:table-cell">Email</th>
            <th class="p-3 text-left font-medium hidden md:table-cell">Contact</th>
            <th class="p-3 text-left font-medium hidden md:table-cell">Description</th>
            <th class="p-3 text-left font-medium whitespace-nowrap hidden md:table-cell">Date</th>
            <th class="p-3 text-right font-medium">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rows as $f)
          <tr class="border-t hover:bg-slate-50 transition-colors {{ !$f->read_at ? 'bg-blue-50/50' : '' }}">
            <td class="p-3 hidden md:table-cell">{{ $f->id }}</td>
            <td class="p-3 font-medium text-slate-800">
              {{ $f->name ?: '—' }}
              @if(!$f->read_at)
                <span class="ml-1 rounded-full bg-blue-600 px-1.5 py-0.5 text-[10px] font-bold text-white">NEW</span>
              @endif
            </td>
            <td class="p-3 hidden md:table-cell">
              @if($f->email)
                <a href="mailto:{{ $f->email }}" class="text-blue-600 hover:underline">{{ $f->email }}</a>
              @else
                —
              @endif
            </td>
            <td class="p-3 hidden md:table-cell">{{ $f->contact ?: '—' }}</td>
            <td class="p-3 hidden md:table-cell text-slate-600">{{ \Illuminate\Support\Str::limit($f->description, 80) }}</td>
            <td class="p-3 hidden md:table-cell whitespace-nowrap text-slate-500">{{ $f->created_at->format('Y-m-d H:i') }}</td>
            <td class="p-3 text-right">
              <a href="{{ route('admin.feedback.show', $f) }}" class="inline-block px-3 py-1 text-sm border border-slate-300 rounded-lg text-slate-700 hover:bg-slate-100 transition">
                View
              </a>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-6">
      {{ $rows->links() }}
    </div>
  @endif
</div>
@endsection
