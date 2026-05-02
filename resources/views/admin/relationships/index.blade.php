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
  <form method="GET" action="{{ route('admin.relationships.index') }}" id="relationshipSearchForm"
        class="bg-white border rounded-xl p-4 mb-6 flex flex-col md:flex-row gap-3 md:items-end">
    <div class="flex-1">
      <label class="text-xs text-slate-600">Search (Parent/Child name, member no, pusta)</label>
      <input type="text" name="q" value="{{ $q ?? '' }}" id="relationshipSearchInput"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="Example: Ram, रम, #DLUMP01, pusta:3">
    </div>

    <div class="w-full md:w-48">
      <label class="text-xs text-slate-600">Relation Type</label>
      <select name="type" id="relationshipTypeFilter" class="border rounded-lg px-3 py-2 w-full">
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
         id="relationshipResetBtn"
         class="px-4 py-2 border rounded-lg hover:bg-slate-50">
        Reset
      </a>
    </div>
  </form>

  {{-- ✅ Add Relationship --}}
  <form method="POST" action="{{ route('admin.relationships.store') }}"
        class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
    @csrf

    <div class="relative">
      <label class="text-xs text-slate-600">Parent *</label>
      <input type="text" data-person-picker="parent_id" class="person-search border rounded-lg px-3 py-2 w-full"
             placeholder="Search parent by ID/name/member no..." autocomplete="off">
      <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">
      <div class="person-selected mt-1 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
      
      <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
      @error('parent_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="relative">
      <label class="text-xs text-slate-600">Child *</label>
      <input type="text" data-person-picker="child_id" class="person-search border rounded-lg px-3 py-2 w-full"
             placeholder="Search child by ID/name/member no..." autocomplete="off">
      <input type="hidden" name="child_id" id="child_id" value="{{ old('child_id') }}">
      <div class="person-selected mt-1 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
      
      <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
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

    @php
      $typeColor = [
        'birth'        => 'bg-emerald-50 text-emerald-700',
        'adoption'     => 'bg-indigo-50 text-indigo-700',
        'step'         => 'bg-amber-50 text-amber-700',
        'guardianship' => 'bg-slate-100 text-slate-700',
      ];
      $grouped = $edges->groupBy('parent_id');
    @endphp

    {{-- desktop table --}}
    <div class="hidden md:block overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <th class="text-left px-4 py-3 w-56">Parent</th>
            <th class="text-left px-4 py-3">Children</th>
          </tr>
        </thead>
        <tbody id="relationshipDesktopBody">
          @forelse($grouped as $parentId => $group)
            @php $parent = $group->first()->parent; @endphp
            <tr class="border-t hover:bg-slate-50/70 align-top">
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-800">{{ $parent->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-400 mt-0.5 space-x-1">
                  @if($parent?->member_no)<span>#{{ $parent->member_no }}</span>@endif
                  @if($parent?->pusta)<span>पु.{{ $parent->pusta }}</span>@endif
                  @if($parent?->birth_date)<span>{{ $parent->birth_date->format('Y') }}</span>@endif
                </div>
                <div class="mt-1 text-[10px] text-slate-400">{{ $group->count() }} {{ $group->count() === 1 ? 'child' : 'children' }}</div>
              </td>

              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1.5">
                  @foreach($group as $e)
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
                      <span class="font-semibold text-slate-800">{{ $e->child->display_name ?? '—' }}</span>
                      @if($e->child?->display_name_np)
                        <span class="text-slate-400">{{ $e->child->display_name_np }}</span>
                      @endif
                      @if($e->child?->pusta)
                        <span class="text-[10px] bg-amber-50 text-amber-600 px-1 rounded">पु.{{ $e->child->pusta }}</span>
                      @endif
                      <span class="text-[10px] {{ $typeColor[$e->relation_type] ?? 'bg-slate-50 text-slate-500' }} px-1.5 rounded-full">
                        {{ ucfirst($e->relation_type) }}
                      </span>
                      <form method="POST" action="{{ route('admin.relationships.destroy', $e) }}"
                            onsubmit="return confirm('Remove {{ addslashes($e->child->display_name ?? '') }} from children of {{ addslashes($parent->display_name ?? '') }}?')"
                            class="inline leading-none">
                        @csrf @method('DELETE')
                        <button type="submit" title="Remove"
                          class="text-slate-300 hover:text-rose-500 transition-colors font-bold text-xs leading-none">✕</button>
                      </form>
                    </span>
                  @endforeach
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="px-4 py-8 text-center text-slate-500">No relationships found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- mobile cards --}}
    <div class="md:hidden" id="relationshipMobileList">
      @forelse($grouped as $parentId => $group)
        @php $parent = $group->first()->parent; @endphp
        <div class="border-t p-4">
          <div class="font-semibold text-slate-800">{{ $parent->display_name ?? '—' }}</div>
          <div class="text-xs text-slate-400 mt-0.5">
            {{ $parent?->member_no ? '#'.$parent->member_no : '' }}
            {{ $parent?->pusta ? ' • पु.'.$parent->pusta : '' }}
          </div>
          <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach($group as $e)
              <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
                <span class="font-semibold">{{ $e->child->display_name ?? '—' }}</span>
                @if($e->child?->pusta)
                  <span class="text-slate-400">पु.{{ $e->child->pusta }}</span>
                @endif
                <span class="text-[10px] {{ $typeColor[$e->relation_type] ?? '' }} px-1.5 rounded-full">
                  {{ ucfirst($e->relation_type) }}
                </span>
                <form method="POST" action="{{ route('admin.relationships.destroy', $e) }}"
                      onsubmit="return confirm('Remove {{ addslashes($e->child->display_name ?? '') }}?')"
                      class="inline leading-none">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-slate-300 hover:text-rose-500 font-bold text-xs">✕</button>
                </form>
              </span>
            @endforeach
          </div>
        </div>
      @empty
        <div class="p-6 text-center text-slate-500">No relationships found.</div>
      @endforelse
    </div>

    <div class="p-3" id="relationshipPagination">
      {{ $edges->links() }}
    </div>
  </div>

</div>

<script>
(() => {
  const searchUrl = @json(route('admin.persons.search'));
  const timers = new WeakMap();
  const controllers = new WeakMap();
  const searchCache = new Map();
  let recentPeople = [];
  let recentLoaded = false;

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }
  function label(p) {
    return [p.display_name, p.display_name_np, p.display_name_limbu].filter(Boolean).join(' / ');
  }
  function meta(p) {
    return [`#${p.id}`, p.member_no ? `Member ${p.member_no}` : null, p.pusta ? `पुस्ता ${p.pusta}` : null, p.gender || null].filter(Boolean).join(' · ');
  }
  function highlight(text, q) {
    const safe = esc(text);
    const term = String(q || '').trim();
    if (!term) return safe;
    const parts = term.split(/\s+/).filter(Boolean).map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    if (!parts.length) return safe;
    return safe.replace(new RegExp(`(${parts.join('|')})`, 'ig'), '<mark class="bg-yellow-100 text-slate-900 rounded px-0.5">$1</mark>');
  }
  function hideResults(wrap) {
    wrap.querySelector('.person-results')?.classList.add('hidden');
  }
  function selectPerson(wrap, person) {
    const hidden = document.getElementById(wrap.dataset.target);
    const selected = wrap.querySelector('.person-selected');
    const input = wrap.querySelector('.person-search');
    hidden.value = person.id;
    selected.innerHTML = `<span class="font-semibold">${esc(label(person))}</span> <span class="text-green-600">(${esc(meta(person))})</span>
      <button type="button" class="ml-2 text-red-600 hover:underline" data-clear>remove</button>`;
    selected.classList.remove('hidden');
    hideResults(wrap);
    input.value = '';
    selected.querySelector('[data-clear]').addEventListener('click', () => {
      hidden.value = '';
      selected.classList.add('hidden');
      input.focus();
      showRecent(wrap);
    });
  }
  function renderPeople(wrap, people, q = '', recent = false) {
    const results = wrap.querySelector('.person-results');
    if (!people.length) {
      results.innerHTML = '<div class="px-3 py-2 text-xs text-slate-400">No person found. Try ID, name, member no, or pusta.</div>';
      results.classList.remove('hidden');
      return;
    }
    results.innerHTML = `${recent ? '<div class="px-3 py-1.5 text-[11px] font-semibold text-slate-400 bg-slate-50 border-b">Recently added</div>' : ''}` + people.map(p => `
      <button type="button" class="w-full text-left px-3 py-2 border-b last:border-0 hover:bg-blue-50" data-person='${esc(JSON.stringify(p))}'>
        <div class="text-sm font-semibold text-slate-800">${highlight(label(p), q)}</div>
        <div class="text-xs text-slate-400">${highlight(meta(p), q)}</div>
      </button>`).join('');
    results.classList.remove('hidden');
    results.querySelectorAll('button[data-person]').forEach(btn => {
      btn.addEventListener('click', () => selectPerson(wrap, JSON.parse(btn.dataset.person)));
    });
  }
  async function loadRecent() {
    if (recentLoaded) return recentPeople;
    const res = await fetch(`${searchUrl}?recent=1`);
    const rows = await res.json();
    recentPeople = Array.isArray(rows) ? rows : [];
    recentLoaded = true;
    return recentPeople;
  }
  async function showRecent(wrap) {
    const results = wrap.querySelector('.person-results');
    results.innerHTML = '<div class="px-3 py-2 text-xs text-slate-400">Loading recently added...</div>';
    results.classList.remove('hidden');
    try {
      renderPeople(wrap, await loadRecent(), '', true);
    } catch (e) {
      results.innerHTML = '<div class="px-3 py-2 text-xs text-red-500">Could not load recent persons.</div>';
    }
  }
  async function searchPeople(input, wrap, q) {
    const results = wrap.querySelector('.person-results');
    const key = q.toLowerCase();
    if (searchCache.has(key)) {
      renderPeople(wrap, searchCache.get(key), q, false);
      return;
    }
    controllers.get(input)?.abort();
    const controller = new AbortController();
    controllers.set(input, controller);
    results.innerHTML = '<div class="px-3 py-2 text-xs text-slate-400">Searching...</div>';
    results.classList.remove('hidden');
    try {
      const res = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, { signal: controller.signal });
      const rows = await res.json();
      const people = Array.isArray(rows) ? rows : [];
      searchCache.set(key, people);
      renderPeople(wrap, people, q, false);
    } catch (e) {
      if (e.name === 'AbortError') return;
      results.innerHTML = '<div class="px-3 py-2 text-xs text-red-500">Search failed. Try again.</div>';
    }
  }

  document.querySelectorAll('.person-search').forEach(input => {
    const wrap = input.closest('.relative');
    wrap.dataset.target = input.dataset.personPicker;

    input.addEventListener('focus', () => {
      if (!input.value.trim()) showRecent(wrap);
    });

    input.addEventListener('input', () => {
      clearTimeout(timers.get(input));
      const q = input.value.trim();
      if (!q) { showRecent(wrap); return; }
      timers.set(input, setTimeout(() => searchPeople(input, wrap, q), 160));
    });
  });

  document.addEventListener('click', e => {
    document.querySelectorAll('.person-results').forEach(box => {
      if (!box.parentElement.contains(e.target)) box.classList.add('hidden');
    });
  });
})();

(() => {
  const form = document.getElementById('relationshipSearchForm');
  const input = document.getElementById('relationshipSearchInput');
  const type = document.getElementById('relationshipTypeFilter');
  const reset = document.getElementById('relationshipResetBtn');
  const desktopBody = document.getElementById('relationshipDesktopBody');
  const mobileList = document.getElementById('relationshipMobileList');
  const pagination = document.getElementById('relationshipPagination');
  const searchUrl = @json(route('admin.relationships.search'));
  const csrf = @json(csrf_token());
  const destroyBase = @json(url('/admin/relationships'));
  let timer = null;
  let controller = null;
  const initialDesktop = desktopBody.innerHTML;
  const initialMobile = mobileList.innerHTML;
  const initialPagination = pagination.innerHTML;

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }

  function highlight(text, q) {
    const safe = esc(text);
    const term = String(q || '').trim().replace(/^#/, '').replace(/^pusta\s*:\s*/i, '');
    if (!term) return safe;
    const parts = term.split(/\s+/).filter(Boolean).map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    if (!parts.length) return safe;
    return safe.replace(new RegExp(`(${parts.join('|')})`, 'ig'), '<mark class="bg-yellow-100 text-slate-900 rounded px-0.5">$1</mark>');
  }

  function typeClass(relationType) {
    return {
      birth: 'bg-emerald-50 text-emerald-700',
      adoption: 'bg-indigo-50 text-indigo-700',
      step: 'bg-amber-50 text-amber-700',
      guardianship: 'bg-slate-100 text-slate-700',
    }[relationType] || 'bg-slate-50 text-slate-500';
  }

  function personMeta(p, q) {
    const parts = [];
    if (p.member_no) parts.push(`#${p.member_no}`);
    if (p.pusta) parts.push(`पु.${p.pusta}`);
    if (p.birth_year) parts.push(p.birth_year);
    return highlight(parts.join(' '), q);
  }

  function grouped(rows) {
    const map = new Map();
    for (const row of rows) {
      const key = row.parent?.id || 'unknown';
      if (!map.has(key)) map.set(key, { parent: row.parent, children: [] });
      map.get(key).children.push(row);
    }
    return Array.from(map.values());
  }

  function childChip(row, q, parentName = '') {
    const child = row.child || {};
    const childName = child.display_name || '—';
    const childNp = child.display_name_np || '';
    const confirmText = JSON.stringify(`Remove ${childName} from children of ${parentName || 'this parent'}?`);
    return `<span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
      <span class="font-semibold text-slate-800">${highlight(childName, q)}</span>
      ${childNp ? `<span class="text-slate-400">${highlight(childNp, q)}</span>` : ''}
      ${child.pusta ? `<span class="text-[10px] bg-amber-50 text-amber-600 px-1 rounded">${highlight('पु.' + child.pusta, q)}</span>` : ''}
      <span class="text-[10px] ${typeClass(row.relation_type)} px-1.5 rounded-full">${esc(row.relation_type.charAt(0).toUpperCase() + row.relation_type.slice(1))}</span>
      <form method="POST" action="${destroyBase}/${row.id}" onsubmit="return confirm(${esc(confirmText)})" class="inline leading-none">
        <input type="hidden" name="_token" value="${csrf}">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" title="Remove" class="text-slate-300 hover:text-rose-500 transition-colors font-bold text-xs leading-none">✕</button>
      </form>
    </span>`;
  }

  function render(rows, q) {
    const groups = grouped(rows);
    pagination.innerHTML = `<div class="text-xs text-slate-500 px-1">${rows.length} matching relationship${rows.length === 1 ? '' : 's'} shown live</div>`;

    if (!groups.length) {
      desktopBody.innerHTML = '<tr><td colspan="2" class="px-4 py-8 text-center text-slate-500">No relationships found.</td></tr>';
      mobileList.innerHTML = '<div class="p-6 text-center text-slate-500">No relationships found.</div>';
      return;
    }

    desktopBody.innerHTML = groups.map(group => {
      const parent = group.parent || {};
      const parentName = parent.display_name || '—';
      return `<tr class="border-t hover:bg-slate-50/70 align-top">
        <td class="px-4 py-3">
          <div class="font-semibold text-slate-800">${highlight(parentName, q)}</div>
          <div class="text-xs text-slate-400 mt-0.5 space-x-1">
            ${parent.member_no ? `<span>${highlight('#' + parent.member_no, q)}</span>` : ''}
            ${parent.pusta ? `<span>${highlight('पु.' + parent.pusta, q)}</span>` : ''}
            ${parent.birth_year ? `<span>${esc(parent.birth_year)}</span>` : ''}
          </div>
          <div class="mt-1 text-[10px] text-slate-400">${group.children.length} ${group.children.length === 1 ? 'child' : 'children'}</div>
        </td>
        <td class="px-4 py-3">
          <div class="flex flex-wrap gap-1.5">${group.children.map(row => childChip(row, q, parentName)).join('')}</div>
        </td>
      </tr>`;
    }).join('');

    mobileList.innerHTML = groups.map(group => {
      const parent = group.parent || {};
      const parentName = parent.display_name || '—';
      return `<div class="border-t p-4">
        <div class="font-semibold text-slate-800">${highlight(parentName, q)}</div>
        <div class="text-xs text-slate-400 mt-0.5">
          ${parent.member_no ? highlight('#' + parent.member_no, q) : ''}
          ${parent.pusta ? ` • ${highlight('पु.' + parent.pusta, q)}` : ''}
        </div>
        <div class="mt-3 flex flex-wrap gap-1.5">${group.children.map(row => childChip(row, q, parentName)).join('')}</div>
      </div>`;
    }).join('');
  }

  async function liveSearch() {
    const q = input.value.trim();
    const relationType = type.value;

    if (!q && !relationType) {
      desktopBody.innerHTML = initialDesktop;
      mobileList.innerHTML = initialMobile;
      pagination.innerHTML = initialPagination;
      return;
    }

    controller?.abort();
    controller = new AbortController();
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (relationType) params.set('type', relationType);
    pagination.innerHTML = '<div class="text-xs text-slate-400 px-1">Searching...</div>';

    try {
      const res = await fetch(`${searchUrl}?${params}`, {
        signal: controller.signal,
        headers: { 'Accept': 'application/json' },
      });
      const data = await res.json();
      render(Array.isArray(data.rows) ? data.rows : [], q);
    } catch (e) {
      if (e.name === 'AbortError') return;
      pagination.innerHTML = '<div class="text-xs text-rose-500 px-1">Search failed. Please try again.</div>';
    }
  }

  function scheduleSearch() {
    clearTimeout(timer);
    timer = setTimeout(liveSearch, 180);
  }

  input.addEventListener('input', scheduleSearch);
  type.addEventListener('change', liveSearch);
  form.addEventListener('submit', e => {
    e.preventDefault();
    liveSearch();
  });
  reset.addEventListener('click', e => {
    e.preventDefault();
    input.value = '';
    type.value = '';
    liveSearch();
  });
})();
</script>
@endsection
