@extends('admin.layout')
@section('title','Admin • Unions')
@section('content')
<div class="py-6">
  <h1 class="text-2xl font-bold mb-4">Unions (Marriage / Partnership)</h1>

  <form method="POST" action="{{ route('admin.unions.store') }}" class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
    @csrf
    <div>
      <label class="text-xs text-slate-600">Spouse 1 *</label>
      <select name="spouse1_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select</option>
        @foreach($people as $p)<option value="{{ $p->id }}">{{ $p->display_name }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-600">Spouse 2 *</label>
      <select name="spouse2_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select</option>
        @foreach($people as $p)<option value="{{ $p->id }}">{{ $p->display_name }}</option>@endforeach
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-600">Type *</label>
      <select name="type" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="marriage">Marriage</option>
        <option value="partnership">Partnership</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-600">Start Date</label>
      <input type="date" name="start_date" class="border rounded-lg px-3 py-2 w-full">
    </div>
    <div class="md:self-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">Add</button>
    </div>
    <div class="md:col-span-5">
      <label class="text-xs text-slate-600">Notes</label>
      <input type="text" name="notes" class="border rounded-lg px-3 py-2 w-full" placeholder="Optional notes...">
    </div>
  </form>

  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-100 text-slate-700">
        <tr>
          <th class="text-left px-4 py-2">Spouse 1</th>
          <th class="text-left px-4 py-2">Spouse 2</th>
          <th class="text-left px-4 py-2">Type</th>
          <th class="text-left px-4 py-2">Start</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($unions as $u)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $u->spouse1->display_name }}</td>
            <td class="px-4 py-2">{{ $u->spouse2->display_name }}</td>
            <td class="px-4 py-2 capitalize">{{ $u->type }}</td>
            <td class="px-4 py-2">{{ optional($u->start_date)->format('Y-m-d') ?? '—' }}</td>
            <td class="px-4 py-2 text-right">
              <form method="POST" action="{{ route('admin.unions.destroy',$u) }}" onsubmit="return confirm('Delete union?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg border text-rose-700 hover:bg-rose-50">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="p-3">{{ $unions->links() }}</div>
  </div>
</div>
@endsection
