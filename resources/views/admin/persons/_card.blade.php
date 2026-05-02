@php
  /** @var \App\Models\Person $p */
  $genderMap   = ['male'=>'bg-blue-100 text-blue-700','female'=>'bg-pink-100 text-pink-700','other'=>'bg-purple-100 text-purple-700','unknown'=>'bg-slate-100 text-slate-500'];
  $genderLabel = ['male'=>'♂ Male','female'=>'♀ Female','other'=>'◌ Other','unknown'=>'? Unknown'];
  $gc       = $genderMap[$p->gender] ?? $genderMap['unknown'];
  $gl       = $genderLabel[$p->gender] ?? $p->gender;
  $avatar   = $p->gender === 'female' ? '👩' : ($p->gender === 'male' ? '👨' : '🧑');
  $isParent = ($p->children_count ?? 0) > 0;
@endphp
<div class="p-4 bg-white border rounded-xl shadow-sm hover:shadow-md transition-shadow">
  <div class="flex items-start gap-3">
    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-100 to-slate-100 flex items-center justify-center text-xl flex-shrink-0">
      {{ $avatar }}
    </div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-slate-800 truncate">{{ $p->display_name }}</div>
      <div class="flex flex-wrap gap-1 mt-0.5">
        @if($p->display_name_np)
          <span class="text-slate-400 text-xs">{{ $p->display_name_np }}</span>
        @endif
        @if($p->display_name_limbu)
          <span class="text-slate-400 text-xs">{{ $p->display_name_limbu }}</span>
        @endif
        @if($p->member_no)
          <span class="text-[10px] text-indigo-500 font-mono">#{{ $p->member_no }}</span>
        @endif
      </div>
      <div class="flex flex-wrap items-center gap-1 mt-1">
        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $gc }}">{{ $gl }}</span>
        @if($p->pusta)
          <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">पुस्ता {{ $p->pusta }}</span>
        @endif
        @if($isParent)
          <span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-semibold"
            title="{{ $p->children_count }} children">🌳 Parent</span>
        @endif
        @if($p->is_deceased)
          <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">✝ Deceased</span>
        @elseif($p->birth_date)
          <span class="text-xs text-slate-400">b. {{ $p->birth_date->format('Y') }}</span>
        @endif
      </div>
    </div>
  </div>
  <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
    <a href="{{ route('admin.persons.edit', $p) }}"
      class="flex-1 text-center px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-slate-50 transition-colors">✏️ Edit</a>

    @if($isParent)
      <div class="flex-1 relative group">
        <button type="button" disabled
          class="w-full px-3 py-1.5 text-xs font-medium rounded-lg border bg-slate-50 text-slate-300 cursor-not-allowed">
          🗑 Delete
        </button>
        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-52 text-center
          text-[11px] bg-slate-800 text-white rounded-lg px-2 py-1.5 opacity-0 group-hover:opacity-100
          pointer-events-none transition-opacity z-10 shadow-lg">
          Cannot delete — this person has {{ $p->children_count }} child{{ $p->children_count > 1 ? 'ren' : '' }}.<br>
          Remove child links first.
        </div>
      </div>
    @else
      <form method="POST" action="{{ route('admin.persons.destroy', $p) }}"
        onsubmit="return confirm('Delete {{ addslashes($p->display_name) }}?')" class="flex-1">
        @csrf @method('DELETE')
        <button class="w-full px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-rose-50 text-rose-600 transition-colors">
          🗑 Delete
        </button>
      </form>
    @endif
  </div>
</div>
