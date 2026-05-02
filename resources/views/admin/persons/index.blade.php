@extends('admin.layout')
@section('title','Admin • Persons')
@section('content')
<div class="py-6">

  {{-- ── Flash messages ───────────────────────────── --}}
  @if(session('success'))
    <div data-flash class="mb-4 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
      <span class="text-lg">✅</span><span>{{ session('success') }}</span>
    </div>
  @endif
  @if(session('error'))
    <div data-flash class="mb-4 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
      <span class="text-lg">⛔</span><span>{{ session('error') }}</span>
    </div>
  @endif

  {{-- ── Page header ─────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
      <h1 class="text-2xl font-bold text-slate-800">Members / Persons</h1>
      <p class="text-xs text-slate-400 mt-0.5">Manage all registered persons in the family tree</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      @if(auth()->user()?->isSuperAdmin())
        <form method="POST" action="{{ route('admin.persons.generate-member-numbers') }}"
          onsubmit="return confirm('Generate member numbers for all existing persons without member no?')">
          @csrf
          <button class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 text-sm font-medium shadow-sm">
            <i class="fa fa-hashtag"></i> Generate Missing Nos.
          </button>
        </form>
      @endif
      <a href="{{ route('admin.persons.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium shadow-sm">
        <i class="fa fa-plus"></i> Add Person
      </a>
    </div>
  </div>

  {{-- ── Stats bar ────────────────────────────────── --}}
  <div class="grid grid-cols-3 gap-3 mb-5">
    <div class="bg-white border rounded-xl px-4 py-3 flex items-center gap-3 shadow-sm">
      <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-lg">👥</div>
      <div>
        <div class="text-xl font-bold text-slate-800" id="statTotal">{{ $total }}</div>
        <div class="text-[11px] text-slate-400">Total Persons</div>
      </div>
    </div>
    <div class="bg-white border rounded-xl px-4 py-3 flex items-center gap-3 shadow-sm">
      <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-lg">🌱</div>
      <div>
        <div class="text-xl font-bold text-slate-800">{{ $alive_ct }}</div>
        <div class="text-[11px] text-slate-400">Alive</div>
      </div>
    </div>
    <div class="bg-white border rounded-xl px-4 py-3 flex items-center gap-3 shadow-sm">
      <div class="w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-lg">🕯️</div>
      <div>
        <div class="text-xl font-bold text-slate-800">{{ $dead_ct }}</div>
        <div class="text-[11px] text-slate-400">Deceased</div>
      </div>
    </div>
  </div>

  {{-- ══════════════════════════════════════════════════
      SEARCH + MULTI-FILTER BAR
  ══════════════════════════════════════════════════ --}}
  <div class="bg-white border rounded-xl shadow-sm mb-5">

    {{-- Name search --}}
    <div class="p-4 border-b border-slate-100">
      <div class="relative">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">🔍</span>
        <input type="text" id="liveSearch"
          class="w-full border rounded-xl pl-9 pr-10 py-2.5 text-sm focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100"
          placeholder="खोज्नुहोस् — Search by English / Nepali / Limbu name, first, middle, last name, member no…"
          value="{{ $q }}" autocomplete="off">
        <span id="liveSearchSpinner"
          class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm pointer-events-none">⏳</span>
      </div>
    </div>

    {{-- Filter row --}}
    <div class="px-4 py-3 flex flex-wrap items-end gap-x-6 gap-y-3">

      {{-- Gender --}}
      <div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Gender</div>
        <div class="flex gap-1" id="genderBtnGroup">
          @foreach(['' => '👥 All', 'male' => '♂ Male', 'female' => '♀ Female', 'other' => 'Other'] as $val => $label)
            <button type="button"
              class="gender-btn px-2.5 py-1.5 text-xs rounded-lg border transition-colors
                {{ ($gender ?? '') === $val
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-slate-600 hover:bg-slate-50 border-slate-200' }}"
              data-gender="{{ $val }}">{{ $label }}</button>
          @endforeach
        </div>
      </div>

      {{-- Pusta --}}
      <div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">
          पुस्ता / Generation
          <span class="normal-case font-normal text-slate-300 ml-1">(e.g. 5 or ५)</span>
        </div>
        <input type="text" id="pustaFilter"
          class="border rounded-lg px-3 py-1.5 w-24 text-sm focus:outline-none focus:border-indigo-400"
          placeholder="5 or ५"
          value="{{ $pusta_filter ?? '' }}" autocomplete="off">
      </div>

      {{-- Status --}}
      <div>
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Status</div>
        <div class="flex gap-1" id="aliveBtnGroup">
          @foreach(['' => 'All', '1' => '🌱 Alive', '0' => '🕯️ Deceased'] as $val => $label)
            <button type="button"
              class="alive-btn px-2.5 py-1.5 text-xs rounded-lg border transition-colors
                {{ ($alive ?? '') === $val
                    ? 'bg-indigo-600 text-white border-indigo-600'
                    : 'bg-white text-slate-600 hover:bg-slate-50 border-slate-200' }}"
              data-alive="{{ $val }}">{{ $label }}</button>
          @endforeach
        </div>
      </div>

      {{-- Right side: result count + clear --}}
      <div class="ml-auto flex items-center gap-3 self-end">
        <button type="button" id="clearAllFilters"
          class="hidden text-xs text-red-500 hover:underline font-medium">✕ Clear all</button>
        <span class="text-xs text-slate-500 font-medium" id="resultCount">
          Showing {{ $people->total() }} persons
        </span>
      </div>

    </div>

    {{-- Active filter pills (shown when any filter is active) --}}
    <div id="activePillsWrap" class="px-4 pb-3 flex-wrap gap-1.5 border-t border-slate-100 pt-2.5" style="display:none">
      {{-- pills injected by JS --}}
    </div>

  </div>

  {{-- ── Persons grid ─────────────────────────────── --}}
  <div id="personsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($people as $p)
      @include('admin.persons._card', ['p' => $p])
    @empty
      <div class="md:col-span-3 text-center py-16 text-slate-400">
        <div class="text-4xl mb-3">🔍</div>
        <p class="font-medium">No persons found</p>
        <a href="{{ route('admin.persons.create') }}"
          class="mt-3 inline-block text-blue-600 text-sm hover:underline">+ Add the first person</a>
      </div>
    @endforelse
  </div>

  {{-- Pagination --}}
  <div class="mt-5" id="paginationWrap">{{ $people->links() }}</div>

  {{-- AJAX empty state --}}
  <div id="ajaxEmpty" class="hidden text-center py-16 text-slate-400">
    <div class="text-4xl mb-3">🔍</div>
    <p class="font-medium" id="ajaxEmptyMsg">No persons found for this search.</p>
    <button type="button" id="ajaxClearBtn"
      class="mt-3 text-blue-600 text-sm hover:underline">Clear filters</button>
  </div>

</div>

<script>
(function () {
  /* ── DOM refs ──────────────────────────────── */
  const searchInput   = document.getElementById('liveSearch');
  const spinner       = document.getElementById('liveSearchSpinner');
  const grid          = document.getElementById('personsGrid');
  const pagination    = document.getElementById('paginationWrap');
  const resultCount   = document.getElementById('resultCount');
  const ajaxEmpty     = document.getElementById('ajaxEmpty');
  const ajaxEmptyMsg  = document.getElementById('ajaxEmptyMsg');
  const pillsWrap     = document.getElementById('activePillsWrap');
  const clearAllBtn   = document.getElementById('clearAllFilters');
  const ajaxClearBtn  = document.getElementById('ajaxClearBtn');
  const genderBtns    = document.querySelectorAll('.gender-btn');
  const aliveBtns     = document.querySelectorAll('.alive-btn');
  const pustaInput    = document.getElementById('pustaFilter');

  const SEARCH_URL  = '{{ route("admin.persons.search") }}';
  const EDIT_BASE   = '{{ url("admin/persons") }}';
  const CSRF        = '{{ csrf_token() }}';

  /* ── Filter state ──────────────────────────── */
  const F = {
    q:      '{{ $q }}',
    gender: '{{ $gender ?? "" }}',
    pusta:  '{{ $pusta_filter ?? "" }}',
    alive:  '{{ $alive ?? "" }}',
  };

  let debTimer = null;

  /* ── Helpers ────────────────────────────────── */
  function anyActive() {
    return F.q || F.gender || F.pusta || F.alive !== '';
  }

  function genderBadge(g) {
    const cls   = { male:'bg-blue-100 text-blue-700', female:'bg-pink-100 text-pink-700', other:'bg-purple-100 text-purple-700', unknown:'bg-slate-100 text-slate-500' };
    const label = { male:'♂ Male', female:'♀ Female', other:'◌ Other', unknown:'? Unknown' };
    return `<span class="text-[10px] font-semibold px-2 py-0.5 rounded-full ${cls[g]??cls.unknown}">${label[g]??g}</span>`;
  }

  function buildCard(p) {
    const np       = p.display_name_np ? `<span class="text-xs text-slate-400">${p.display_name_np}</span>` : '';
    const limbu    = p.display_name_limbu ? `<span class="text-xs text-slate-400">${p.display_name_limbu}</span>` : '';
    const mno      = p.member_no ? `<span class="text-[10px] text-indigo-500 font-mono">#${p.member_no}</span>` : '';
    const pusta    = p.pusta ? `<span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">पुस्ता ${p.pusta}</span>` : '';
    const parent   = p.has_children ? `<span class="text-[10px] bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-semibold" title="Has children">🌳 Parent</span>` : '';
    const deceased = p.is_deceased ? `<span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-semibold">✝ Deceased</span>` : '';
    const birth    = p.birth_year ? `<span class="text-xs text-slate-400">b. ${p.birth_year}</span>` : '';
    const avatar   = p.gender === 'female' ? '👩' : p.gender === 'male' ? '👨' : '🧑';

    const editUrl   = `${EDIT_BASE}/${p.id}/edit`;
    const deleteUrl = `${EDIT_BASE}/${p.id}`;
    const safeName  = p.display_name.replace(/'/g,"\\'");

    const delBtn = p.has_children
      ? `<div class="flex-1 relative group">
           <button disabled class="w-full px-3 py-1.5 text-xs font-medium rounded-lg border bg-slate-50 text-slate-300 cursor-not-allowed">🗑 Delete</button>
           <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-52 text-center text-[11px] bg-slate-800 text-white rounded-lg px-2 py-1.5 opacity-0 group-hover:opacity-100 pointer-events-none transition-opacity z-10 shadow-lg">
             Cannot delete — has children.<br>Remove child links first.
           </div>
         </div>`
      : `<form method="POST" action="${deleteUrl}" onsubmit="return confirm('Delete ${safeName}?')" class="flex-1">
           <input type="hidden" name="_token" value="${CSRF}">
           <input type="hidden" name="_method" value="DELETE">
           <button class="w-full px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-rose-50 text-rose-600 transition-colors">🗑 Delete</button>
         </form>`;

    return `<div class="p-4 bg-white border rounded-xl shadow-sm hover:shadow-md transition-shadow">
  <div class="flex items-start gap-3">
    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-100 to-slate-100 flex items-center justify-center text-xl flex-shrink-0">${avatar}</div>
    <div class="flex-1 min-w-0">
      <div class="font-semibold text-slate-800 truncate">${p.display_name}</div>
      <div class="flex flex-wrap gap-1 mt-0.5">${np}${limbu}${mno}</div>
      <div class="flex flex-wrap items-center gap-1 mt-1">${genderBadge(p.gender)} ${pusta} ${parent} ${deceased} ${birth}</div>
    </div>
  </div>
  <div class="flex items-center gap-2 mt-3 pt-3 border-t border-slate-100">
    <a href="${editUrl}" class="flex-1 text-center px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-slate-50 transition-colors">✏️ Edit</a>
    ${delBtn}
  </div>
</div>`;
  }

  /* ── Active pills ───────────────────────────── */
  function updatePills() {
    const pills = [];
    const genderLabel = { male:'♂ Male', female:'♀ Female', other:'Other' };
    if (F.q)      pills.push({ label:`Name: "${F.q}"`,    clear:() => { F.q = ''; searchInput.value = ''; } });
    if (F.gender) pills.push({ label:genderLabel[F.gender] ?? F.gender, clear:() => { F.gender = ''; setGroupActive(genderBtns, ''); } });
    if (F.pusta)  pills.push({ label:`पुस्ता: ${F.pusta}`, clear:() => { F.pusta = ''; pustaInput.value = ''; } });
    if (F.alive === '1') pills.push({ label:'🌱 Alive',   clear:() => { F.alive = ''; setGroupActive(aliveBtns, ''); } });
    if (F.alive === '0') pills.push({ label:'🕯️ Deceased', clear:() => { F.alive = ''; setGroupActive(aliveBtns, ''); } });

    if (pills.length) {
      pillsWrap.style.display = 'flex';
      clearAllBtn.classList.remove('hidden');
      pillsWrap.innerHTML = pills.map((pill, i) =>
        `<button type="button" data-pill="${i}"
          class="pill-btn inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 border border-indigo-200 text-indigo-700 rounded-full text-xs font-medium hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-colors">
          ${pill.label} <span class="ml-0.5 opacity-60">✕</span>
        </button>`
      ).join('');
      pillsWrap.querySelectorAll('.pill-btn').forEach((btn, i) => {
        btn.addEventListener('click', () => { pills[i].clear(); fireSearch(); });
      });
    } else {
      pillsWrap.style.display = 'none';
      clearAllBtn.classList.add('hidden');
    }
  }

  /* ── Button group active state ──────────────── */
  function setGroupActive(buttons, value) {
    buttons.forEach(b => {
      const isActive = b.dataset.gender === value || b.dataset.alive === value;
      b.classList.toggle('bg-indigo-600', isActive);
      b.classList.toggle('text-white',    isActive);
      b.classList.toggle('border-indigo-600', isActive);
      b.classList.toggle('bg-white',    !isActive);
      b.classList.toggle('text-slate-600', !isActive);
      b.classList.toggle('border-slate-200', !isActive);
    });
  }

  /* ── Main search ────────────────────────────── */
  async function fireSearch() {
    updatePills();

    if (!anyActive()) {
      // No filters — show server-rendered paginated results
      ajaxEmpty.classList.add('hidden');
      grid.style.display = '';
      pagination.style.display = '';
      resultCount.textContent = `Showing {{ $people->total() }} persons`;
      // If we came from an AJAX state, reload for pagination
      if (grid.dataset.ajax === '1') location.reload();
      return;
    }

    spinner.classList.remove('hidden');
    try {
      const params = new URLSearchParams();
      if (F.q)          params.set('q',      F.q);
      if (F.gender)     params.set('gender', F.gender);
      if (F.pusta)      params.set('pusta',  F.pusta);
      if (F.alive !== '') params.set('alive', F.alive);

      const res  = await fetch(`${SEARCH_URL}?${params}`);
      const data = await res.json();
      spinner.classList.add('hidden');

      pagination.style.display = 'none';
      grid.dataset.ajax = '1';

      if (!data.length) {
        grid.innerHTML = '';
        grid.style.display = 'none';
        ajaxEmpty.classList.remove('hidden');
        const parts = [];
        if (F.q)      parts.push(`"${F.q}"`);
        if (F.gender) parts.push(F.gender);
        if (F.pusta)  parts.push(`पुस्ता ${F.pusta}`);
        ajaxEmptyMsg.textContent = `No persons found for: ${parts.join(', ') || 'current filters'}`;
        resultCount.textContent = '0 persons';
        return;
      }

      ajaxEmpty.classList.add('hidden');
      grid.style.display = '';
      grid.innerHTML = data.map(buildCard).join('');
      resultCount.textContent = `${data.length} person${data.length !== 1 ? 's' : ''} found`;
    } catch (e) {
      spinner.classList.add('hidden');
    }
  }

  /* ── Event listeners ────────────────────────── */

  // Name search — debounced
  searchInput.addEventListener('input', function () {
    clearTimeout(debTimer);
    F.q = this.value.trim();
    debTimer = setTimeout(fireSearch, 280);
  });

  // Pusta filter — debounced
  pustaInput.addEventListener('input', function () {
    clearTimeout(debTimer);
    F.pusta = this.value.trim();
    debTimer = setTimeout(fireSearch, 280);
  });

  // Gender buttons
  genderBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      F.gender = this.dataset.gender;
      setGroupActive(genderBtns, F.gender);
      clearTimeout(debTimer);
      fireSearch();
    });
  });

  // Alive/status buttons
  aliveBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      F.alive = this.dataset.alive;
      setGroupActive(aliveBtns, F.alive);
      clearTimeout(debTimer);
      fireSearch();
    });
  });

  // Clear all
  clearAllBtn.addEventListener('click', () => {
    F.q = ''; F.gender = ''; F.pusta = ''; F.alive = '';
    searchInput.value = '';
    pustaInput.value  = '';
    setGroupActive(genderBtns, '');
    setGroupActive(aliveBtns,  '');
    fireSearch();
  });

  ajaxClearBtn.addEventListener('click', () => clearAllBtn.click());

  // Auto-dismiss flash messages
  setTimeout(() => document.querySelectorAll('[data-flash]').forEach(el => el.remove()), 4000);

  // Run on load if server sent filters (keeps pills in sync)
  if (anyActive()) updatePills();

})();
</script>

@endsection
