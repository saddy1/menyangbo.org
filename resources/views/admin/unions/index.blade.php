{{-- resources/views/admin/unions/index.blade.php --}}
@extends('admin.layout')
@section('title', 'Admin • Unions')

@section('content')
<div class="py-6 max-w-6xl mx-auto px-3">

  <div class="flex items-center justify-between gap-3 flex-wrap mb-4">
    <h1 class="text-2xl font-bold">Unions (Marriage / Partnership)</h1>
    <div class="text-sm text-slate-500">
      Tip: Search by <span class="font-semibold">Name</span>, <span class="font-semibold">Nepali/Limbu Name</span>,
      <span class="font-semibold">Member No</span> (ex: <span class="font-mono">#DLUMP01</span>) or <span class="font-mono">pusta:3</span>
    </div>
  </div>

  {{-- ✅ Search / Filter --}}
  <form method="GET" action="{{ route('admin.unions.index') }}" id="unionSearchForm"
        class="bg-white border rounded-xl p-4 mb-6 flex flex-col md:flex-row gap-3 md:items-end">
    <div class="flex-1">
      <label class="text-xs text-slate-600">Search (Name / Nepali / Limbu / Member No)</label>
      <input type="text" name="q" value="{{ $q ?? '' }}" id="unionSearchInput"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="Type name, Nepali name, member no. Example: #DLUMP01 or pusta:3">
    </div>

    <div class="w-full md:w-40">
      <label class="text-xs text-slate-600">Pusta</label>
      <input type="text" name="pusta" value="{{ $pusta ?? '' }}" id="unionPustaInput"
             class="border rounded-lg px-3 py-2 w-full"
             placeholder="e.g. 3">
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
        Search
      </button>
      <a href="{{ route('admin.unions.index') }}"
         id="unionResetBtn"
         class="px-4 py-2 border rounded-lg hover:bg-slate-50">
        Reset
      </a>
    </div>
  </form>

  {{-- ✅ Add union form --}}
  <form method="POST" action="{{ route('admin.unions.store') }}"
        class="bg-white border rounded-xl p-4 grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
    @csrf

    <div class="relative">
      <label class="text-xs text-slate-600">Spouse 1 *</label>
      <input type="text" data-person-picker="spouse1_id" class="person-search border rounded-lg px-3 py-2 w-full"
             placeholder="Search spouse 1 by ID/name/member no..." autocomplete="off">
      <input type="hidden" name="spouse1_id" id="spouse1_id" value="{{ old('spouse1_id') }}">
      <div class="person-selected mt-1 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
      
      <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
      @error('spouse1_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div class="relative">
      <label class="text-xs text-slate-600">Spouse 2 *</label>
      <input type="text" data-person-picker="spouse2_id" class="person-search border rounded-lg px-3 py-2 w-full"
             placeholder="Search spouse 2 by ID/name/member no..." autocomplete="off">
      <input type="hidden" name="spouse2_id" id="spouse2_id" value="{{ old('spouse2_id') }}">
      <div class="person-selected mt-1 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
      
      <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
      @error('spouse2_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="text-xs text-slate-600">Type *</label>
      <select name="type" class="border rounded-lg px-3 py-2 w-full" required>
        <option value="married" @selected(old('type','married')=='married')>Married</option>
        <option value="partner" @selected(old('type')=='partner')>Partner</option>
        <option value="divorced" @selected(old('type')=='divorced')>Divorced</option>
        <option value="widowed" @selected(old('type')=='widowed')>Widowed</option>
        <option value="separated" @selected(old('type')=='separated')>Separated</option>
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
  @php
    $typeColor = [
      'married'   => 'bg-pink-50 text-pink-700',
      'marriage'  => 'bg-pink-50 text-pink-700',
      'partner'   => 'bg-indigo-50 text-indigo-700',
      'divorced'  => 'bg-slate-100 text-slate-500',
      'widowed'   => 'bg-amber-50 text-amber-700',
      'separated' => 'bg-slate-100 text-slate-500',
      'other'     => 'bg-slate-50 text-slate-600',
    ];
    $grouped = $unions->groupBy('spouse1_id');
  @endphp

  <div class="bg-white border rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-100 text-slate-700">
          <tr>
            <th class="text-left px-4 py-3 w-56">Person</th>
            <th class="text-left px-4 py-3">Partners / Unions</th>
          </tr>
        </thead>

        <tbody id="unionTableBody">
          @forelse($grouped as $spouse1Id => $group)
            @php
              $spouse1 = $group->first()->spouse1;
            @endphp
            <tr class="border-t hover:bg-slate-50/70 align-top">
              <td class="px-4 py-3">
                <div class="font-semibold text-slate-800">{{ $spouse1->display_name ?? '—' }}</div>
                <div class="text-xs text-slate-400 mt-0.5 space-x-1">
                  @if($spouse1?->display_name_np)<span>{{ $spouse1->display_name_np }}</span>@endif
                  @if($spouse1?->member_no)<span>#{{ $spouse1->member_no }}</span>@endif
                  @if($spouse1?->pusta)<span>पु.{{ $spouse1->pusta }}</span>@endif
                </div>
                <div class="mt-1 text-[10px] text-slate-400">{{ $group->count() }} {{ $group->count() === 1 ? 'union' : 'unions' }}</div>
              </td>

              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1.5">
                  @foreach($group as $u)
                    @php
                      $partner = $u->spouse2;
                      $start = $u->start_date instanceof \Carbon\CarbonInterface ? $u->start_date->format('Y-m-d') : $u->start_date;
                      $tc = $typeColor[$u->type] ?? 'bg-slate-50 text-slate-600';
                    @endphp
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
                      <span class="font-semibold text-slate-800">{{ $partner->display_name ?? '—' }}</span>
                      @if($partner?->display_name_np)
                        <span class="text-slate-400">{{ $partner->display_name_np }}</span>
                      @endif
                      @if($partner?->pusta)
                        <span class="text-[10px] bg-amber-50 text-amber-600 px-1 rounded">पु.{{ $partner->pusta }}</span>
                      @endif
                      <span class="text-[10px] {{ $tc }} px-1.5 rounded-full capitalize">{{ $u->type ?? '—' }}</span>
                      @if($start)
                        <span class="text-[10px] text-slate-400">{{ $start }}</span>
                      @endif
                      <form method="POST" action="{{ route('admin.unions.destroy', $u) }}"
                            onsubmit="return confirm('Delete union between {{ addslashes($spouse1->display_name ?? '') }} and {{ addslashes($partner->display_name ?? '') }}?')"
                            class="inline leading-none">
                        @csrf @method('DELETE')
                        <button type="submit" title="Delete union"
                          class="text-slate-300 hover:text-rose-500 transition-colors font-bold text-xs leading-none">✕</button>
                      </form>
                    </span>
                  @endforeach
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="px-4 py-6 text-center text-slate-500">
                No unions found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-3" id="unionPagination">
      {{ $unions->links() }}
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
</script>
<script>
(() => {
  const form = document.getElementById('unionSearchForm');
  const qInput = document.getElementById('unionSearchInput');
  const pustaInput = document.getElementById('unionPustaInput');
  const resetBtn = document.getElementById('unionResetBtn');
  const tableBody = document.getElementById('unionTableBody');
  const pagination = document.getElementById('unionPagination');
  const searchUrl = @json(route('admin.unions.search'));
  const destroyBase = @json(url('/admin/unions'));
  const csrf = @json(csrf_token());
  const initialTable = tableBody.innerHTML;
  const initialPagination = pagination.innerHTML;
  let timer = null;
  let controller = null;

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }

  function highlight(text, q, pusta) {
    const safe = esc(text);
    const terms = [];
    const qTerm = String(q || '').trim().replace(/^#/, '').replace(/^pusta\s*:\s*/i, '');
    if (qTerm) terms.push(...qTerm.split(/\s+/));
    if (pusta) terms.push(String(pusta).trim());
    const parts = terms.filter(Boolean).map(t => t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    if (!parts.length) return safe;
    return safe.replace(new RegExp(`(${parts.join('|')})`, 'ig'), '<mark class="bg-yellow-100 text-slate-900 rounded px-0.5">$1</mark>');
  }

  function typeClass(type) {
    return {
      married: 'bg-pink-50 text-pink-700',
      marriage: 'bg-pink-50 text-pink-700',
      partner: 'bg-indigo-50 text-indigo-700',
      divorced: 'bg-slate-100 text-slate-500',
      widowed: 'bg-amber-50 text-amber-700',
      separated: 'bg-slate-100 text-slate-500',
      other: 'bg-slate-50 text-slate-600',
    }[type] || 'bg-slate-50 text-slate-600';
  }

  function grouped(rows) {
    const map = new Map();
    for (const row of rows) {
      const key = row.spouse1?.id || 'unknown';
      if (!map.has(key)) map.set(key, { person: row.spouse1, unions: [] });
      map.get(key).unions.push(row);
    }
    return Array.from(map.values());
  }

  function personMeta(p, q, pusta) {
    const parts = [];
    if (p.display_name_np) parts.push(p.display_name_np);
    if (p.member_no) parts.push(`#${p.member_no}`);
    if (p.pusta) parts.push(`पु.${p.pusta}`);
    return highlight(parts.join(' '), q, pusta);
  }

  function unionChip(row, q, pusta, spouse1Name = '') {
    const partner = row.spouse2 || {};
    const partnerName = partner.display_name || '—';
    const confirmText = JSON.stringify(`Delete union between ${spouse1Name || 'this person'} and ${partnerName}?`);

    return `<span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs shadow-sm">
      <span class="font-semibold text-slate-800">${highlight(partnerName, q, pusta)}</span>
      ${partner.display_name_np ? `<span class="text-slate-400">${highlight(partner.display_name_np, q, pusta)}</span>` : ''}
      ${partner.pusta ? `<span class="text-[10px] bg-amber-50 text-amber-600 px-1 rounded">${highlight('पु.' + partner.pusta, q, pusta)}</span>` : ''}
      <span class="text-[10px] ${typeClass(row.type)} px-1.5 rounded-full capitalize">${esc(row.type || '—')}</span>
      ${row.start_date ? `<span class="text-[10px] text-slate-400">${esc(row.start_date)}</span>` : ''}
      <form method="POST" action="${destroyBase}/${row.id}" onsubmit="return confirm(${esc(confirmText)})" class="inline leading-none">
        <input type="hidden" name="_token" value="${csrf}">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit" title="Delete union" class="text-slate-300 hover:text-rose-500 transition-colors font-bold text-xs leading-none">✕</button>
      </form>
    </span>`;
  }

  function render(rows, q, pusta) {
    const groups = grouped(rows);
    pagination.innerHTML = `<div class="text-xs text-slate-500 px-1">${rows.length} matching union${rows.length === 1 ? '' : 's'} shown live</div>`;

    if (!groups.length) {
      tableBody.innerHTML = '<tr><td colspan="2" class="px-4 py-6 text-center text-slate-500">No unions found.</td></tr>';
      return;
    }

    tableBody.innerHTML = groups.map(group => {
      const person = group.person || {};
      const personName = person.display_name || '—';
      return `<tr class="border-t hover:bg-slate-50/70 align-top">
        <td class="px-4 py-3">
          <div class="font-semibold text-slate-800">${highlight(personName, q, pusta)}</div>
          <div class="text-xs text-slate-400 mt-0.5 space-x-1">${personMeta(person, q, pusta)}</div>
          <div class="mt-1 text-[10px] text-slate-400">${group.unions.length} ${group.unions.length === 1 ? 'union' : 'unions'}</div>
        </td>
        <td class="px-4 py-3">
          <div class="flex flex-wrap gap-1.5">${group.unions.map(row => unionChip(row, q, pusta, personName)).join('')}</div>
        </td>
      </tr>`;
    }).join('');
  }

  async function liveSearch() {
    const q = qInput.value.trim();
    const pusta = pustaInput.value.trim();
    if (!q && !pusta) {
      tableBody.innerHTML = initialTable;
      pagination.innerHTML = initialPagination;
      return;
    }

    controller?.abort();
    controller = new AbortController();
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (pusta) params.set('pusta', pusta);
    pagination.innerHTML = '<div class="text-xs text-slate-400 px-1">Searching...</div>';

    try {
      const res = await fetch(`${searchUrl}?${params}`, {
        signal: controller.signal,
        headers: { 'Accept': 'application/json' },
      });
      const data = await res.json();
      render(Array.isArray(data.rows) ? data.rows : [], q, pusta);
    } catch (e) {
      if (e.name === 'AbortError') return;
      pagination.innerHTML = '<div class="text-xs text-rose-500 px-1">Search failed. Please try again.</div>';
    }
  }

  function scheduleSearch() {
    clearTimeout(timer);
    timer = setTimeout(liveSearch, 180);
  }

  qInput.addEventListener('input', scheduleSearch);
  pustaInput.addEventListener('input', scheduleSearch);
  form.addEventListener('submit', e => {
    e.preventDefault();
    liveSearch();
  });
  resetBtn.addEventListener('click', e => {
    e.preventDefault();
    qInput.value = '';
    pustaInput.value = '';
    liveSearch();
  });
})();
</script>
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
