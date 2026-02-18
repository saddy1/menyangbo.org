@extends('admin.layout')
@section('title','Admin • Relationships')

@section('content')
<div class="py-6 max-w-6xl mx-auto px-3">

  <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
    <h1 class="text-2xl font-bold">Parent → Child Relationships</h1>
    <div class="text-sm text-slate-500">
      Search by Name, Nepali Name, Member No (<span class="font-mono">#DLUMP01</span>), Pusta (<span class="font-mono">pusta:3</span>)
    </div>
  </div>

  {{-- ✅ Search / Filter --}}
  <form method="GET" action="{{ route('admin.relationships.index') }}"
        class="bg-white border rounded-xl p-4 mb-6 flex flex-col md:flex-row gap-3 md:items-end">
    <div class="flex-1">
      <label class="text-xs text-slate-600">Search (Parent/Child name, member no, pusta)</label>
      <input type="text" name="q" value="{{ $q ?? '' }}"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="Example: Ram, रम, #DLUMP01, pusta:3">
    </div>

    <div class="w-full md:w-48">
      <label class="text-xs text-slate-600">Relation Type</label>
      <select name="type" class="border rounded-lg px-3 py-2 w-full">
        <option value="">All</option>
        @foreach (['birth'=>'Birth','adoption'=>'Adoption','step'=>'Step','guardianship'=>'Guardianship'] as $k => $v)
          <option value="{{ $k }}" @selected(($type ?? '') === $k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Search
      </button>
      <a href="{{ route('admin.relationships.index') }}"
         class="px-4 py-2 border rounded-lg hover:bg-slate-50">
        Reset
      </a>
    </div>
  </form>

  {{-- ✅ Add Relationship --}}
  <form method="POST" action="{{ route('admin.relationships.store') }}"
        class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
    @csrf

    <div>
      <label class="text-xs text-slate-600">Parent *</label>
      <select name="parent_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select parent</option>
        @foreach($people as $p)
          <option value="{{ $p->id }}" @selected(old('parent_id') == $p->id)>
            {{ $p->display_name }}
            {{ $p->member_no ? ' (#'.$p->member_no.')' : '' }}
            {{ $p->pusta ? ' • पु.'.$p->pusta : '' }}
          </option>
        @endforeach
      </select>
      @error('parent_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Child *</label>
      <select name="child_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select child</option>
        @foreach($people as $p)
          <option value="{{ $p->id }}" @selected(old('child_id') == $p->id)>
            {{ $p->display_name }}
            {{ $p->member_no ? ' (#'.$p->member_no.')' : '' }}
            {{ $p->pusta ? ' • पु.'.$p->pusta : '' }}
          </option>
        @endforeach
      </select>
      @error('child_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Relation Type *</label>
      <select name="relation_type" class="border rounded-lg px-3 py-2 w-full" required>
        @foreach (['birth'=>'Birth','adoption'=>'Adoption','step'=>'Step','guardianship'=>'Guardianship'] as $k => $v)
          <option value="{{ $k }}" @selected(old('relation_type','birth') === $k)>{{ $v }}</option>
        @endforeach
      </select>
      @error('relation_type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:self-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">
        Add
      </button>
    </div>

    <div class="md:col-span-4">
      <label class="text-xs text-slate-600">Notes</label>
      <input type="text" name="notes" class="border rounded-lg px-3 py-2 w-full"
             value="{{ old('notes') }}" placeholder="Optional notes...">
      @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>
  </form>

  {{-- ✅ Relationship list --}}
  <div class="bg-white border rounded-xl overflow-hidden">

    {{-- desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <th class="text-left px-4 py-3">Parent</th>
            <th class="text-center px-2 py-3">→</th>
            <th class="text-left px-4 py-3">Child</th>
            <th class="text-left px-4 py-3">Type</th>
            <th class="px-4 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse($edges as $e)
            <tr class="border-t hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="font-semibold">{{ $e->parent->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ $e->parent->member_no ? '#'.$e->parent->member_no : '' }}
                  {{ $e->parent->pusta ? ' • पु.'.$e->parent->pusta : '' }}
                  {{ $e->parent->birth_date ? ' • '.$e->parent->birth_date->format('Y-m-d') : '' }}
                </div>
              </td>

              <td class="px-2 py-3 text-center text-slate-400 font-bold">→</td>

              <td class="px-4 py-3">
                <div class="font-semibold">{{ $e->child->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ $e->child->member_no ? '#'.$e->child->member_no : '' }}
                  {{ $e->child->pusta ? ' • पु.'.$e->child->pusta : '' }}
                  {{ $e->child->birth_date ? ' • '.$e->child->birth_date->format('Y-m-d') : '' }}
                </div>
              </td>

              <td class="px-4 py-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                  {{ $e->relation_type === 'birth' ? 'bg-emerald-50 text-emerald-700' : '' }}
                  {{ $e->relation_type === 'adoption' ? 'bg-indigo-50 text-indigo-700' : '' }}
                  {{ $e->relation_type === 'step' ? 'bg-amber-50 text-amber-700' : '' }}
                  {{ $e->relation_type === 'guardianship' ? 'bg-slate-100 text-slate-700' : '' }}
                ">
                  {{ ucfirst($e->relation_type) }}
                </span>

                @if(!empty($e->notes))
                  <div class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $e->notes }}</div>
                @endif
              </td>

              <td class="px-4 py-3 text-right">
                <form method="POST" action="{{ route('admin.relationships.destroy',$e) }}"
                      onsubmit="return confirm('Delete relationship?')">
                  @csrf
                  @method('DELETE')
                  <button class="px-3 py-1.5 rounded-lg border text-rose-700 hover:bg-rose-50">
                    Delete
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-4 py-8 text-center text-slate-500">No relationships found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- mobile cards --}}
    <div class="md:hidden">
      @forelse($edges as $e)
        <div class="border-t p-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full
              {{ $e->relation_type === 'birth' ? 'bg-emerald-50 text-emerald-700' : '' }}
              {{ $e->relation_type === 'adoption' ? 'bg-indigo-50 text-indigo-700' : '' }}
              {{ $e->relation_type === 'step' ? 'bg-amber-50 text-amber-700' : '' }}
              {{ $e->relation_type === 'guardianship' ? 'bg-slate-100 text-slate-700' : '' }}
            ">
              {{ ucfirst($e->relation_type) }}
            </span>

            <form method="POST" action="{{ route('admin.relationships.destroy',$e) }}"
                  onsubmit="return confirm('Delete relationship?')">
              @csrf @method('DELETE')
              <button class="px-3 py-1.5 rounded-lg border text-rose-700 hover:bg-rose-50 text-xs">
                Delete
              </button>
            </form>
          </div>

          <div class="mt-3 grid grid-cols-1 gap-2">
            <div class="rounded-xl border p-3">
              <div class="text-xs text-slate-500 font-semibold mb-1">Parent</div>
              <div class="font-semibold">{{ $e->parent->display_name ?? '—' }}</div>
              <div class="text-xs text-slate-500 mt-1">
                {{ $e->parent->member_no ? '#'.$e->parent->member_no : '' }}
                {{ $e->parent->pusta ? ' • पु.'.$e->parent->pusta : '' }}
              </div>
            </div>

            <div class="text-center text-slate-400 font-bold">↓</div>

            <div class="rounded-xl border p-3">
              <div class="text-xs text-slate-500 font-semibold mb-1">Child</div>
              <div class="font-semibold">{{ $e->child->display_name ?? '—' }}</div>
              <div class="text-xs text-slate-500 mt-1">
                {{ $e->child->member_no ? '#'.$e->child->member_no : '' }}
                {{ $e->child->pusta ? ' • पु.'.$e->child->pusta : '' }}
              </div>
            </div>

            @if(!empty($e->notes))
              <div class="text-xs text-slate-500">
                <span class="font-semibold">Notes:</span> {{ $e->notes }}
              </div>
            @endif
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-slate-500">No relationships found.</div>
      @endforelse
    </div>

    <div class="p-3">
      {{ $edges->links() }}
    </div>
  </div>

</div>
@endsection
