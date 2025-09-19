@extends('admin.layout')
@section('title','Admin • Relationships')
@section('content')
<div class="py-6">
  <h1 class="text-2xl font-bold mb-4">Parent → Child</h1>

  <form method="POST" action="{{ route('admin.relationships.store') }}" class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
    @csrf
    <div>
      <label class="text-xs text-slate-600">Parent *</label>
      <select name="parent_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select parent</option>
        @foreach($people as $p)
          <option value="{{ $p->id }}">{{ $p->display_name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-600">Child *</label>
      <select name="child_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select child</option>
        @foreach($people as $p)
          <option value="{{ $p->id }}">{{ $p->display_name }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-xs text-slate-600">Relation Type *</label>
      <select name="relation_type" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="birth">Birth</option>
        <option value="adoption">Adoption</option>
        <option value="step">Step</option>
        <option value="guardianship">Guardianship</option>
      </select>
    </div>
    <div class="md:self-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">Add</button>
    </div>
    <div class="md:col-span-4">
      <label class="text-xs text-slate-600">Notes</label>
      <input type="text" name="notes" class="border rounded-lg px-3 py-2 w-full" placeholder="Optional notes...">
    </div>
  </form>

  <div class="bg-white border rounded-xl overflow-hidden">
    <table class="w-full text-sm">
      <thead class="bg-slate-100 text-slate-700">
        <tr>
          <th class="text-left px-4 py-2">Parent</th>
          <th class="text-left px-4 py-2">Child</th>
          <th class="text-left px-4 py-2">Type</th>
          <th class="px-4 py-2"></th>
        </tr>
      </thead>
      <tbody>
        @foreach($edges as $e)
          <tr class="border-t">
            <td class="px-4 py-2">{{ $e->parent->display_name }}</td>
            <td class="px-4 py-2">{{ $e->child->display_name }}</td>
            <td class="px-4 py-2 capitalize">{{ $e->relation_type }}</td>
            <td class="px-4 py-2 text-right">
              <form method="POST" action="{{ route('admin.relationships.destroy',$e) }}" onsubmit="return confirm('Delete relationship?')">
                @csrf @method('DELETE')
                <button class="px-3 py-1.5 rounded-lg border text-rose-700 hover:bg-rose-50">Delete</button>
              </form>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="p-3">{{ $edges->links() }}</div>
  </div>
</div>
@endsection
