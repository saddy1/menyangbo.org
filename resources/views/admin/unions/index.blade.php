{{-- resources/views/admin/unions/index.blade.php --}}
@extends('admin.layout')
@section('title', 'Admin • Unions')

@section('content')
<div class="py-6 max-w-6xl mx-auto px-3">

  <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
    <h1 class="text-2xl font-bold">Unions (Marriage / Partnership)</h1>
    <div class="text-sm text-slate-500">
      Tip: Search by <span class="font-semibold">Name</span>, <span class="font-semibold">Nepali Name</span>,
      <span class="font-semibold">Member No</span> (ex: <span class="font-mono">#DLUMP01</span>) or <span class="font-mono">pusta:3</span>
    </div>
  </div>

  {{-- ✅ Search / Filter --}}
  <form method="GET" action="{{ route('admin.unions.index') }}"
        class="bg-white border rounded-xl p-4 mb-6 flex flex-col md:flex-row gap-3 md:items-end">
    <div class="flex-1">
      <label class="text-xs text-slate-600">Search (Name / Nepali Name / Member No)</label>
      <input type="text" name="q" value="{{ $q ?? '' }}"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="Type name, Nepali name, member no. Example: #DLUMP01 or pusta:3">
    </div>

    <div class="w-full md:w-40">
      <label class="text-xs text-slate-600">Pusta</label>
      <input type="text" name="pusta" value="{{ $pusta ?? '' }}"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="e.g. 3">
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Search
      </button>
      <a href="{{ route('admin.unions.index') }}"
         class="px-4 py-2 border rounded-lg hover:bg-slate-50">
        Reset
      </a>
    </div>
  </form>

  {{-- ✅ Add union form --}}
  <form method="POST" action="{{ route('admin.unions.store') }}"
        class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
    @csrf

    <div>
      <label class="text-xs text-slate-600">Spouse 1 *</label>
      <select name="spouse1_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select</option>
        @foreach ($people as $pp)
          <option value="{{ $pp->id }}" @selected(old('spouse1_id') == $pp->id)>
            {{ $pp->display_name }}
            {{ $pp->member_no ? ' (#'.$pp->member_no.')' : '' }}
            {{ $pp->pusta ? ' • पु.'.$pp->pusta : '' }}
          </option>
        @endforeach
      </select>
      @error('spouse1_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Spouse 2 *</label>
      <select name="spouse2_id" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="">Select</option>
        @foreach ($people as $pp)
          <option value="{{ $pp->id }}" @selected(old('spouse2_id') == $pp->id)>
            {{ $pp->display_name }}
            {{ $pp->member_no ? ' (#'.$pp->member_no.')' : '' }}
            {{ $pp->pusta ? ' • पु.'.$pp->pusta : '' }}
          </option>
        @endforeach
      </select>
      @error('spouse2_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Type *</label>
      <select name="type" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="marriage" @selected(old('type','marriage')=='marriage')>Marriage</option>
        <option value="partnership" @selected(old('type')=='partnership')>Partnership</option>
        <option value="other" @selected(old('type')=='other')>Other</option>
      </select>
      @error('type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Start Date (BS)</label>
      <input type="text" name="start_date" maxlength="10" placeholder="YYYY-MM-DD"
             class="border rounded-lg px-3 py-2 w-full tracking-widest"
             value="{{ old('start_date') }}" oninput="formatDateInput(this)">
      @error('start_date') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="md:self-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 w-full">
        Add
      </button>
    </div>

    <div class="md:col-span-5">
      <label class="text-xs text-slate-600">Notes</label>
      <input type="text" name="notes" class="border rounded-lg px-3 py-2 w-full"
             value="{{ old('notes') }}" placeholder="Optional notes...">
      @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>
  </form>

  {{-- ✅ Unions list --}}
  <div class="bg-white border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <th class="text-left px-4 py-3">Spouse 1</th>
            <th class="text-left px-4 py-3">Spouse 2</th>
            <th class="text-left px-4 py-3">Type</th>
            <th class="text-left px-4 py-3">Start</th>
            <th class="px-4 py-3 text-right">Action</th>
          </tr>
        </thead>

        <tbody>
          @forelse ($unions as $u)
            <tr class="border-t hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="font-semibold">{{ $u->spouse1->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ !empty($u->spouse1?->member_no) ? ('#'.$u->spouse1->member_no) : '' }}
                  {{ !empty($u->spouse1?->pusta) ? (' • पु.'.$u->spouse1->pusta) : '' }}
                </div>
              </td>

              <td class="px-4 py-3">
                <div class="font-semibold">{{ $u->spouse2->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-500">
                  {{ !empty($u->spouse2?->member_no) ? ('#'.$u->spouse2->member_no) : '' }}
                  {{ !empty($u->spouse2?->pusta) ? (' • पु.'.$u->spouse2->pusta) : '' }}
                </div>
              </td>

              <td class="px-4 py-3 capitalize">{{ $u->type ?? '—' }}</td>

              <td class="px-4 py-3">
                {{-- your start_date may be string or date --}}
                @php
                  $start = $u->start_date;
                  if ($start instanceof \Carbon\CarbonInterface) $start = $start->format('Y-m-d');
                @endphp
                {{ $start ?: '—' }}
              </td>

              <td class="px-4 py-3 text-right">
                <form method="POST" action="{{ route('admin.unions.destroy', $u) }}"
                      onsubmit="return confirm('Delete union?')">
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
              <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                No unions found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-3">
      {{ $unions->links() }}
    </div>
  </div>

</div>

<script>
function formatDateInput(el) {
  let val = (el.value || '').replace(/[^0-9]/g, '');
  if (val.length > 4 && val.length <= 6) {
    val = val.slice(0,4) + '-' + val.slice(4);
  } else if (val.length > 6) {
    val = val.slice(0,4) + '-' + val.slice(4,6) + '-' + val.slice(6,8);
  }
  el.value = val;
}
</script>
@endsection
