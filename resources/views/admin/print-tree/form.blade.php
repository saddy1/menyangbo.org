@extends('admin.layout')
@section('title', 'Print Family Tree')

@php
  $np = fn ($n) => \App\Http\Controllers\Admin\PrintTreeController::toNepaliDigits($n);
  $pustaKeys = $pustas->keys();
@endphp

@section('content')
<div class="py-6 max-w-2xl">

  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Print Family Tree</h1>
      <p class="text-sm text-gray-500 mt-1">Choose where the tree starts, and the पुस्ता where it ends.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← Dashboard</a>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <form method="POST" action="{{ route('admin.print-tree.generate') }}" class="space-y-7" id="printForm">
      @csrf

      {{-- ── Step 1: start ── --}}
      <div>
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3">1 · Start from</div>

        <label class="block text-sm font-semibold text-gray-700 mb-2">
          One person and their descendants <span class="font-normal text-gray-400">(optional)</span>
        </label>
        <div class="relative">
          <input type="text" id="rootSearch" placeholder="Search by name or ID…" autocomplete="off"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none pr-10">
          <input type="hidden" name="root_id" id="rootId" value="">
          <button type="button" id="rootClear" aria-label="Clear selected person"
            class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg leading-none">×</button>
        </div>
        <div id="rootResults" class="hidden mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-64 overflow-y-auto z-50 relative"></div>
        <div id="rootSelected" class="hidden mt-2 items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5">
          <span id="rootSelectedIcon" class="text-lg"></span>
          <div>
            <div id="rootSelectedName" class="font-semibold text-blue-900 text-sm"></div>
            <div id="rootSelectedMeta" class="text-xs text-blue-600"></div>
          </div>
        </div>
        @error('root_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror

        {{-- Browse by pusta to pick a person --}}
        <details class="mt-3">
          <summary class="text-xs text-blue-600 cursor-pointer select-none">Browse people by पुस्ता…</summary>
          <div class="flex flex-wrap gap-2 mt-3 mb-3">
            @foreach($pustaKeys as $pustaVal)
              <button type="button" onclick="showPustaMembers({{ $pustaVal }})" data-pusta-filter="{{ $pustaVal }}"
                class="pusta-filter-btn px-3 py-1.5 rounded-full border border-gray-200 text-xs font-bold text-gray-600 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 transition-all">
                {{ $np($pustaVal) }}
              </button>
            @endforeach
          </div>
          <div id="pustaMemberList" class="hidden">
            <div class="flex items-center justify-between mb-2">
              <div class="text-xs font-semibold text-gray-600" id="pustaMemberLabel"></div>
              <button type="button" onclick="closePustaList()" class="text-xs text-gray-400 hover:text-gray-600">✕ Close</button>
            </div>
            <div id="pustaMemberGrid" class="grid grid-cols-1 gap-1.5 max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-2"></div>
          </div>
        </details>

        <div class="flex items-center gap-2 my-4">
          <div class="h-px flex-1 bg-gray-200"></div>
          <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider px-2">or</span>
          <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        <label for="fromPusta" class="block text-sm font-semibold text-gray-700 mb-2">
          A whole पुस्ता — every member of it <span class="font-normal text-gray-400">(From पुस्ता)</span>
        </label>
        <select name="from_pusta" id="fromPusta"
          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none disabled:bg-gray-100 disabled:text-gray-500">
          <option value="">— choose —</option>
          @foreach($pustas as $p => $count)
            <option value="{{ $p }}" @selected(old('from_pusta') == $p)>पुस्ता {{ $np($p) }} ({{ $p }}) — {{ $count }} people</option>
          @endforeach
        </select>
        <p id="fromHint" class="hidden mt-1 text-xs text-blue-600"></p>
        @error('from_pusta')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
      </div>

      {{-- ── Step 2: end ── --}}
      <div>
        <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-3">2 · Print until</div>
        <label for="toPusta" class="block text-sm font-semibold text-gray-700 mb-2">To पुस्ता</label>
        <select name="to_pusta" id="toPusta" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          @foreach($pustas as $p => $count)
            <option value="{{ $p }}" @selected(old('to_pusta', $pustaKeys->last()) == $p)>पुस्ता {{ $np($p) }} ({{ $p }})</option>
          @endforeach
        </select>
        <p id="rangeSummary" class="mt-2 text-sm font-semibold text-gray-700"></p>
        @error('to_pusta')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
      </div>

      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <div class="font-semibold mb-1">ℹ️ कसरी काम गर्छ?</div>
        <ul class="list-disc ml-4 space-y-1 text-xs">
          <li>व्यक्ति छान्नुभयो भने उहाँको पुस्ताबाट सुरु हुन्छ। नछान्नुभए "From पुस्ता" का सबै सदस्यबाट सुरु हुन्छ।</li>
          <li>"To पुस्ता" सम्मका सबै सन्तान देखाउँछ। जीवनसाथी card भित्रै देखिन्छ।</li>
          <li>ठूलो रूख A4 मा पढ्न मिल्ने बनाउन अर्को पेजमा <strong>Split across pages</strong> छान्नुहोस्।</li>
        </ul>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition text-sm flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Preview tree
      </button>
    </form>
  </div>
</div>

<script>
const personsByPusta = @json($personsByPusta);
const NP = '०१२३४५६७८९';
const toNp  = n => String(n).replace(/\d/g, d => NP[d]);
const toInt = s => { const t = String(s ?? '').trim().replace(/[०-९]/g, d => NP.indexOf(d)); return /^\d+$/.test(t) ? +t : null; };

const fromSel = document.getElementById('fromPusta');
const toSel   = document.getElementById('toPusta');
const fromHint = document.getElementById('fromHint');
const summary  = document.getElementById('rangeSummary');
let rootPusta  = null;   // pusta of selected person (int) or null

function startPusta() { return document.getElementById('rootId').value ? rootPusta : (fromSel.value ? +fromSel.value : null); }

function updateSummary() {
  const s = startPusta(), t = +toSel.value;
  // Hide "to" options earlier than start
  [...toSel.options].forEach(o => { o.disabled = s !== null && +o.value < s; });
  if (s !== null && t < s) { toSel.value = s; }
  if (s === null) { summary.textContent = ''; return; }
  const n = +toSel.value - s + 1;
  summary.textContent = `पुस्ता ${toNp(s)} → ${toNp(+toSel.value)}  ·  ${n} generation${n > 1 ? 's' : ''}`;
}
fromSel.addEventListener('change', updateSummary);
toSel.addEventListener('change', updateSummary);

// ── Browse members by pusta ──
function showPustaMembers(pusta) {
  const members = personsByPusta[pusta] || [];
  document.querySelectorAll('.pusta-filter-btn').forEach(b => {
    const active = +b.dataset.pustaFilter === pusta;
    b.classList.toggle('border-blue-500', active); b.classList.toggle('bg-blue-50', active);
    b.classList.toggle('text-blue-700', active);   b.classList.toggle('border-gray-200', !active);
  });
  const grid = document.getElementById('pustaMemberGrid');
  document.getElementById('pustaMemberLabel').textContent = `पुस्ता ${toNp(pusta)} — ${members.length} members`;
  grid.innerHTML = members.length ? members.map(p => `
    <button type="button" data-person='${escapeHtml(JSON.stringify({ id: p.id, name: p.name, gender: p.gender, pusta: p.pusta }))}'
      class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white border border-gray-200 hover:border-blue-400 hover:bg-blue-50 text-left w-full group">
      <span class="text-base flex-shrink-0">${icon(p.gender)}</span>
      <div class="min-w-0">
        <div class="font-semibold text-sm text-gray-800 group-hover:text-blue-700 truncate">${escapeHtml(p.name)}</div>
        <div class="text-xs text-gray-400">ID: ${p.id}${p.birth_year ? ' · b.' + p.birth_year : ''}${p.name_np ? ' · ' + escapeHtml(p.name_np) : ''}</div>
      </div>
      <span class="ml-auto text-xs text-blue-400 opacity-0 group-hover:opacity-100 flex-shrink-0">Select →</span>
    </button>`).join('')
    : '<div class="text-xs text-gray-400 text-center py-4">No members found for this pusta.</div>';
  grid.querySelectorAll('[data-person]').forEach(btn => btn.addEventListener('click', () => {
    const p = JSON.parse(btn.dataset.person); selectPerson(p.id, p.name, p.gender, p.pusta);
  }));
  document.getElementById('pustaMemberList').classList.remove('hidden');
}
function closePustaList() { document.getElementById('pustaMemberList').classList.add('hidden'); }

// ── Name/ID search ──
const searchInput = document.getElementById('rootSearch');
const hiddenInput = document.getElementById('rootId');
const resultsDiv  = document.getElementById('rootResults');
const selectedDiv = document.getElementById('rootSelected');
const clearBtn    = document.getElementById('rootClear');
let searchTimer = null;

searchInput.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const term = searchInput.value.trim();
  if (!term) { resultsDiv.classList.add('hidden'); return; }
  searchTimer = setTimeout(() => fetchResults(term), 300);
});
document.addEventListener('click', e => {
  if (!e.target.closest('#rootSearch') && !e.target.closest('#rootResults')) resultsDiv.classList.add('hidden');
});

function fetchResults(term) {
  fetch(`/people/search?term=${encodeURIComponent(term)}`).then(r => r.json()).then(renderResults).catch(() => {});
}
function renderResults(people) {
  resultsDiv.innerHTML = people.length ? people.map(p => `
    <button type="button" data-person='${escapeHtml(JSON.stringify({ id: p.id, name: p.display_name, gender: p.gender, pusta: toInt(p.pusta) }))}'
      class="w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 border-b border-gray-50 last:border-0">
      <span class="text-lg">${icon(p.gender)}</span>
      <div>
        <div class="font-medium text-sm text-gray-800">${escapeHtml(p.display_name)}</div>
        <div class="text-xs text-gray-400">ID: ${p.id}${p.pusta ? ' · पुस्ता ' + escapeHtml(p.pusta) : ''}</div>
      </div>
    </button>`).join('')
    : '<div class="px-4 py-3 text-sm text-gray-400">कोही भेटिएन।</div>';
  resultsDiv.classList.remove('hidden');
  resultsDiv.querySelectorAll('[data-person]').forEach(btn => btn.addEventListener('click', () => {
    const p = JSON.parse(btn.dataset.person); selectPerson(p.id, p.name, p.gender, p.pusta);
  }));
}

function selectPerson(id, name, gender, pusta) {
  hiddenInput.value = id;
  rootPusta = pusta ?? null;
  searchInput.value = name;
  searchInput.classList.add('hidden');
  resultsDiv.classList.add('hidden');
  clearBtn.classList.remove('hidden');
  document.getElementById('rootSelectedIcon').textContent = icon(gender);
  document.getElementById('rootSelectedName').textContent = name;
  document.getElementById('rootSelectedMeta').textContent = `ID: ${id}` + (rootPusta ? ` · पुस्ता ${toNp(rootPusta)}` : ' · ⚠ no पुस्ता saved');
  selectedDiv.classList.remove('hidden'); selectedDiv.style.display = 'flex';

  // Person decides the start pusta → lock "From पुस्ता"
  fromSel.value = rootPusta ?? '';
  fromSel.disabled = true;
  fromHint.textContent = 'Starts from the selected person. Clear the person to choose a whole पुस्ता.';
  fromHint.classList.remove('hidden');
  closePustaList();
  updateSummary();
}

clearBtn.addEventListener('click', () => {
  hiddenInput.value = ''; rootPusta = null;
  searchInput.value = ''; searchInput.classList.remove('hidden');
  clearBtn.classList.add('hidden');
  selectedDiv.classList.add('hidden'); selectedDiv.style.display = '';
  fromSel.disabled = false; fromSel.value = '';
  fromHint.classList.add('hidden');
  searchInput.focus();
  updateSummary();
});

function icon(g) { return g === 'male' ? '👨' : g === 'female' ? '👩' : '👤'; }
function escapeHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
updateSummary();
</script>
@endsection
