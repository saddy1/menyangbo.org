@extends('admin.layout')
@section('title', 'Print Family Tree')

@section('content')
<div class="py-6 max-w-2xl">

  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Print Family Tree</h1>
      <p class="text-sm text-gray-500 mt-1">Choose a starting member and how many pusta (generations) to print.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">← Dashboard</a>
  </div>

  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
    <form method="POST" action="{{ route('admin.print-tree.generate') }}" class="space-y-7">
      @csrf

      {{-- ── Root person picker (name/ID search) ── --}}
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          Starting Person (Root Node)
        </label>
        <div class="relative">
          <input type="text" id="rootSearch" placeholder="Search by name or ID…"
            autocomplete="off"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none pr-10">
          <input type="hidden" name="root_id" id="rootId">
          <button type="button" id="rootClear"
            class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg leading-none">×</button>
        </div>
        <div id="rootResults"
          class="hidden mt-1 border border-gray-200 rounded-lg bg-white shadow-lg max-h-64 overflow-y-auto z-50 relative">
        </div>
        {{-- Selected card --}}
        <div id="rootSelected"
          class="hidden mt-2 items-center gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2.5">
          <span id="rootSelectedIcon" class="text-lg"></span>
          <div>
            <div id="rootSelectedName" class="font-semibold text-blue-900 text-sm"></div>
            <div id="rootSelectedMeta" class="text-xs text-blue-600"></div>
          </div>
        </div>
        @if($errors->has('root_id'))
          <p class="mt-1 text-xs text-rose-600">{{ $errors->first('root_id') }}</p>
        @endif
      </div>

      {{-- ── Browse members by Pusta ── --}}
      <div>
        <div class="flex items-center gap-2 mb-3">
          <div class="h-px flex-1 bg-gray-200"></div>
          <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider px-2">OR Browse by Pusta</span>
          <div class="h-px flex-1 bg-gray-200"></div>
        </div>

        {{-- Pusta filter buttons --}}
        <div class="flex flex-wrap gap-2 mb-3" id="pustaFilterBtns">
          @foreach($pustas as $pustaVal)
            <button type="button"
              onclick="showPustaMembers('{{ $pustaVal }}')"
              data-pusta-filter="{{ $pustaVal }}"
              class="pusta-filter-btn px-3 py-1.5 rounded-full border border-gray-200 text-xs font-bold text-gray-600 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 transition-all">
              पुस्ता {{ $pustaVal }}
            </button>
          @endforeach
          @if($pustas->isEmpty())
            <span class="text-xs text-gray-400 italic">No pusta data found.</span>
          @endif
        </div>

        {{-- Member list (hidden until pusta selected) --}}
        <div id="pustaMemberList" class="hidden">
          <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-semibold text-gray-600" id="pustaMemberLabel"></div>
            <button type="button" onclick="closePustaList()"
              class="text-xs text-gray-400 hover:text-gray-600">✕ Close</button>
          </div>
          <div id="pustaMemberGrid"
            class="grid grid-cols-1 gap-1.5 max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-gray-50 p-2">
          </div>
        </div>
      </div>

      {{-- ── Pusta (generation) count ── --}}
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-3">
          पुस्ता — Generations to Print
        </label>

        <div class="grid grid-cols-5 gap-2 mb-3" id="pustaButtons">
          @foreach([10, 20, 30, 40, 50, 60, 70, 80, 90, 100] as $p)
            <button type="button"
              onclick="setPusta({{ $p }})"
              data-pusta="{{ $p }}"
              class="pusta-btn border-2 border-gray-200 rounded-xl py-3 text-center font-bold text-sm hover:border-blue-400 hover:bg-blue-50 transition-all group">
              <div class="text-blue-700 text-base font-extrabold group-hover:scale-110 transition-transform">{{ $p }}</div>
              <div class="text-xs text-gray-400 font-normal">पुस्ता</div>
            </button>
          @endforeach
        </div>

        <div class="flex items-center gap-3 mt-2">
          <span class="text-xs text-gray-500">Custom:</span>
          <input type="number" name="max_pusta" id="pustaInput"
            min="1" max="100" value="{{ old('max_pusta', 10) }}"
            class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center font-bold focus:ring-2 focus:ring-blue-500 outline-none"
            oninput="highlightPusta(parseInt(this.value))">
          <span class="text-xs text-gray-500">पुस्तासम्म</span>
        </div>

        @if($errors->has('max_pusta'))
          <p class="mt-1 text-xs text-rose-600">{{ $errors->first('max_pusta') }}</p>
        @endif
      </div>

      {{-- Info box --}}
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800">
        <div class="font-semibold mb-1">ℹ️ कसरी काम गर्छ?</div>
        <ul class="list-disc ml-4 space-y-1 text-xs">
          <li>चुनेको व्यक्तिबाट सुरु भएर तल सबै पुस्ताका सन्तानहरू देखाउँछ।</li>
          <li>जीवनसाथी (spouse) प्रत्येक card भित्रै देखिन्छ — compact layout।</li>
          <li>पुस्ता अनुसार browse गर्न माथिको "Browse by Pusta" प्रयोग गर्नुहोस्।</li>
          <li>ठूलो पुस्ता (५०+) मा ढिलाइ हुन सक्छ।</li>
        </ul>
      </div>

      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl transition text-sm flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Generate & Preview Tree
      </button>
    </form>
  </div>
</div>

<script>
// ── Person data grouped by pusta ──────────────────────────────────────────
const personsByPusta = @json($personsByPusta);

// ── Pusta generation count ────────────────────────────────────────────────
function setPusta(val) {
  document.getElementById('pustaInput').value = val;
  highlightPusta(val);
}

function highlightPusta(val) {
  document.querySelectorAll('.pusta-btn').forEach(btn => {
    const active = parseInt(btn.dataset.pusta) === val;
    btn.classList.toggle('border-blue-500', active);
    btn.classList.toggle('bg-blue-50', active);
    btn.classList.toggle('border-gray-200', !active);
  });
}

highlightPusta(parseInt(document.getElementById('pustaInput').value));

// ── Browse members by pusta ───────────────────────────────────────────────
function showPustaMembers(pusta) {
  const members = personsByPusta[pusta] || [];

  // Highlight active pusta filter btn
  document.querySelectorAll('.pusta-filter-btn').forEach(b => {
    const active = b.dataset.pustaFilter == pusta;
    b.classList.toggle('border-blue-500', active);
    b.classList.toggle('bg-blue-50', active);
    b.classList.toggle('text-blue-700', active);
    b.classList.toggle('border-gray-200', !active);
    b.classList.toggle('text-gray-600', !active);
  });

  const label = document.getElementById('pustaMemberLabel');
  const grid  = document.getElementById('pustaMemberGrid');
  const list  = document.getElementById('pustaMemberList');

  label.textContent = `पुस्ता ${pusta} — ${members.length} members`;

  if (!members.length) {
    grid.innerHTML = '<div class="text-xs text-gray-400 text-center py-4">No members found for this pusta.</div>';
  } else {
    grid.innerHTML = members.map(p => `
      <button type="button"
        data-person='${escapeAttr(JSON.stringify({ id: p.id, name: p.name, gender: p.gender, pusta: p.pusta }))}'
        class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white border border-gray-200 hover:border-blue-400 hover:bg-blue-50 text-left transition-all w-full group">
        <span class="text-base flex-shrink-0">${p.gender === 'male' ? '👨' : p.gender === 'female' ? '👩' : '👤'}</span>
        <div class="min-w-0">
          <div class="font-semibold text-sm text-gray-800 group-hover:text-blue-700 truncate">${escapeHtml(p.name)}</div>
          <div class="text-xs text-gray-400">
            ID: ${p.id}${p.birth_year ? ' · b.' + p.birth_year : ''}${p.name_np ? ' · ' + escapeHtml(p.name_np) : ''}
          </div>
        </div>
        <span class="ml-auto text-xs text-blue-400 opacity-0 group-hover:opacity-100 flex-shrink-0">Select →</span>
      </button>
    `).join('');
    grid.querySelectorAll('[data-person]').forEach(btn => {
      btn.addEventListener('click', () => {
        const p = JSON.parse(btn.dataset.person);
        selectPerson(p.id, p.name, p.gender, p.pusta);
      });
    });
  }

  list.classList.remove('hidden');
}

function closePustaList() {
  document.getElementById('pustaMemberList').classList.add('hidden');
  document.querySelectorAll('.pusta-filter-btn').forEach(b => {
    b.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
    b.classList.add('border-gray-200', 'text-gray-600');
  });
}

// ── Name/ID search ────────────────────────────────────────────────────────
const searchInput  = document.getElementById('rootSearch');
const hiddenInput  = document.getElementById('rootId');
const resultsDiv   = document.getElementById('rootResults');
const selectedDiv  = document.getElementById('rootSelected');
const clearBtn     = document.getElementById('rootClear');
const selectedName = document.getElementById('rootSelectedName');
const selectedMeta = document.getElementById('rootSelectedMeta');
const selectedIcon = document.getElementById('rootSelectedIcon');

let searchTimer = null;

searchInput.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const term = searchInput.value.trim();
  if (term.length < 1) { resultsDiv.classList.add('hidden'); return; }
  searchTimer = setTimeout(() => fetchResults(term), 300);
});

searchInput.addEventListener('focus', () => {
  if (searchInput.value.trim()) fetchResults(searchInput.value.trim());
});

document.addEventListener('click', e => {
  if (!e.target.closest('#rootSearch') && !e.target.closest('#rootResults'))
    resultsDiv.classList.add('hidden');
});

function fetchResults(term) {
  fetch(`/people/search?term=${encodeURIComponent(term)}`)
    .then(r => r.json())
    .then(data => renderResults(data))
    .catch(() => {});
}

function renderResults(people) {
  if (!people.length) {
    resultsDiv.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">कोही भेटिएन।</div>';
    resultsDiv.classList.remove('hidden');
    return;
  }
  resultsDiv.innerHTML = people.map(p => `
    <button type="button" data-person='${escapeAttr(JSON.stringify({ id: p.id, name: p.display_name, gender: p.gender, pusta: p.pusta || '' }))}'
      class="w-full text-left flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 cursor-pointer border-b border-gray-50 last:border-0">
      <span class="text-lg">${p.gender === 'male' ? '👨' : p.gender === 'female' ? '👩' : '👤'}</span>
      <div>
        <div class="font-medium text-sm text-gray-800">${escapeHtml(p.display_name)}</div>
        <div class="text-xs text-gray-400">ID: ${p.id}${p.pusta ? ' · पुस्ता ' + p.pusta : ''}</div>
      </div>
    </button>
  `).join('');
  resultsDiv.classList.remove('hidden');

  resultsDiv.querySelectorAll('[data-person]').forEach(btn => {
    btn.addEventListener('click', () => {
      const p = JSON.parse(btn.dataset.person);
      selectPerson(p.id, p.name, p.gender, p.pusta);
    });
  });
}

// ── Select a person as root (shared by search + pusta browse) ────────────
function selectPerson(id, name, gender, pusta) {
  hiddenInput.value = id;
  searchInput.value = name;
  searchInput.classList.add('hidden');
  resultsDiv.classList.add('hidden');
  clearBtn.classList.remove('hidden');

  selectedIcon.textContent = gender === 'male' ? '👨' : gender === 'female' ? '👩' : '👤';
  selectedName.textContent = name;
  selectedMeta.textContent = `ID: ${id}${pusta ? ' · पुस्ता ' + pusta : ''}`;
  selectedDiv.classList.remove('hidden');
  selectedDiv.style.display = 'flex';

  // Close the pusta browser after selection
  closePustaList();
}

clearBtn.addEventListener('click', () => {
  hiddenInput.value = '';
  searchInput.value = '';
  searchInput.classList.remove('hidden');
  clearBtn.classList.add('hidden');
  selectedDiv.classList.add('hidden');
  selectedDiv.style.display = '';
  searchInput.focus();
});

function escapeHtml(str) {
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function escapeAttr(str) {
  return escapeHtml(str);
}
</script>
@endsection
