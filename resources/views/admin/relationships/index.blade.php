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

  @if(!empty($prefill['child_id']) && empty($prefill['parent_id']) && !old('child_id'))
    <div class="mb-3 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-900">
      <span>🔗</span>
      <span><strong>{{ $prefill['child_id']['display_name'] }}</strong> सन्तानको रूपमा छानिएको छ — अब तल अभिभावक (बुबा/आमा) खोजेर छान्नुहोस्।</span>
    </div>
  @endif

  {{-- ✅ Add Relationship --}}
  <form method="POST" action="{{ route('admin.relationships.store') }}" id="relationshipAddForm"
        class="bg-white border border-slate-200 rounded-2xl shadow-sm mb-6">
    @csrf

    <div class="flex flex-wrap items-center gap-3 border-b border-slate-100 px-4 py-3">
      <div class="font-bold text-slate-900">नयाँ सम्बन्ध थप्नुहोस्</div>
      <ol class="ml-auto flex flex-wrap items-center gap-1.5 text-[11px] font-semibold" id="relSteps">
        @foreach (['अभिभावक', 'सन्तान', 'जन्म क्रम', 'थप्नुहोस्'] as $i => $step)
          <li data-step="{{ $i + 1 }}" class="rel-step flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-slate-400">
            <span class="rel-step-num flex h-4 w-4 items-center justify-center rounded-full bg-slate-200 text-[10px] text-white">{{ $i + 1 }}</span>
            {{ $step }}
          </li>
        @endforeach
      </ol>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4">
      <div class="relative">
        <label class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-600">
          <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-[10px] text-white">१</span>
          अभिभावक (Parent) *
        </label>
        <input type="text" data-person-picker="parent_id" class="person-search border border-slate-300 rounded-lg px-3 py-2 w-full focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"
               placeholder="ID, नाम वा सदस्य नं. ले खोज्नुहोस्…" autocomplete="off">
        <input type="hidden" name="parent_id" id="parent_id" value="{{ old('parent_id') }}">
        <div class="person-selected mt-1.5 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
        <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
        @error('parent_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>

      <div class="relative">
        <label class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-600">
          <span class="flex h-5 w-5 items-center justify-center rounded-full bg-slate-900 text-[10px] text-white">२</span>
          सन्तान (Child) *
        </label>
        <input type="text" data-person-picker="child_id" class="person-search border border-slate-300 rounded-lg px-3 py-2 w-full focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none"
               placeholder="ID, नाम वा सदस्य नं. ले खोज्नुहोस्…" autocomplete="off">
        <input type="hidden" name="child_id" id="child_id" value="{{ old('child_id') }}">
        <div class="person-selected mt-1.5 hidden rounded-lg border border-green-200 bg-green-50 px-2 py-1 text-xs text-green-800"></div>
        <div class="person-results hidden absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
        @error('child_id') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
    </div>

    {{-- Step 3: birth order (filled by JS once parent/child are picked) --}}
    <div id="relationshipPreview" class="hidden mx-4 mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4"></div>
    @error('birth_order') <div class="mx-4 -mt-2 mb-3 text-xs text-rose-600">{{ $message }}</div> @enderror

    <div class="grid grid-cols-1 md:grid-cols-[180px_1fr_auto] gap-3 items-end border-t border-slate-100 bg-slate-50/60 px-4 py-3 rounded-b-2xl">
      <div>
        <label class="text-xs font-semibold text-slate-600">सम्बन्धको प्रकार *</label>
        <select name="relation_type" class="border border-slate-300 rounded-lg px-3 py-2 w-full bg-white" required>
          @foreach (['birth'=>'Birth','adoption'=>'Adoption','step'=>'Step','guardianship'=>'Guardianship'] as $k => $v)
            <option value="{{ $k }}" @selected(old('relation_type','birth') === $k)>{{ $v }}</option>
          @endforeach
        </select>
        @error('relation_type') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <div>
        <label class="text-xs font-semibold text-slate-600">Notes</label>
        <input type="text" name="notes" class="border border-slate-300 rounded-lg px-3 py-2 w-full bg-white"
               value="{{ old('form_type') === 'marriage' ? '' : old('notes') }}" placeholder="Optional notes...">
        @error('notes') <div class="text-xs text-rose-600 mt-1">{{ $message }}</div> @enderror
      </div>
      <button id="relationshipAddBtn" disabled
        class="px-6 py-2 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed">
        + सम्बन्ध थप्नुहोस्
      </button>
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
                <button type="button" data-edit-children="{{ $parentId }}"
                  class="mt-2 inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">
                  ✎ सम्पादन / क्रम
                </button>
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
          <div class="flex items-start gap-2">
            <div class="min-w-0 flex-1">
              <div class="font-semibold text-slate-800">{{ $parent->display_name ?? '—' }}</div>
              <div class="text-xs text-slate-400 mt-0.5">
                {{ $parent?->member_no ? '#'.$parent->member_no : '' }}
                {{ $parent?->pusta ? ' • पु.'.$parent->pusta : '' }}
              </div>
            </div>
            <button type="button" data-edit-children="{{ $parentId }}"
              class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">
              ✎ सम्पादन
            </button>
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

{{-- Edit all children of one parent: order, gender, pusta, relation type --}}
<div id="childrenEditor" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-slate-900/40 p-0 sm:p-6" role="dialog" aria-modal="true">
  <div class="flex max-h-[92vh] w-full sm:max-w-3xl flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl border border-slate-200 bg-white shadow-2xl">
    <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-4 sm:px-5 py-3">
      <div class="min-w-0">
        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">सन्तान सम्पादन</div>
        <div class="truncate font-bold text-slate-900" data-ce="title">—</div>
        <div class="text-[11px] text-slate-500" data-ce="meta"></div>
      </div>
      <button type="button" data-ce="close" class="ml-auto h-8 w-8 shrink-0 rounded-full text-lg leading-none text-slate-500 hover:bg-slate-200/60" aria-label="बन्द गर्नुहोस्">×</button>
    </div>

    <div class="flex-1 overflow-y-auto bg-white px-3 sm:px-5 py-4" data-ce="body">
      <div class="py-8 text-center text-sm text-slate-400">Loading…</div>
    </div>

    <div class="flex flex-wrap items-center gap-2 border-t border-slate-100 bg-slate-50/80 px-4 sm:px-5 py-3">
      <span class="text-[11px] text-slate-500" data-ce="status">सन्तान क्रम १–१० मध्ये छान्नुहोस् · एउटै नम्बर दुई जनालाई पनि दिन मिल्छ।</span>
      <button type="button" data-ce="close" class="ml-auto rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">रद्द</button>
      <button type="button" data-ce="save" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">सेभ गर्नुहोस्</button>
    </div>
  </div>
</div>

<script>
(() => {
  const modal = document.getElementById('childrenEditor');
  const $ = n => modal.querySelector(`[data-ce="${n}"]`);
  const body = $('body'), saveBtn = $('save'), status = $('status');
  const baseUrl = @json(url('/admin/relationships/parent'));
  const csrf = @json(csrf_token());
  const MAX = {{ \App\Support\SiblingOrder::MAX }};
  const np = n => String(n ?? '').replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
  const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  const GROUPS = [
    { key: 'male',   title: 'छोरा',  box: 'border-blue-100 bg-blue-50/40',   pill: 'bg-blue-100 text-blue-700' },
    { key: 'female', title: 'छोरी',  box: 'border-pink-100 bg-pink-50/40',   pill: 'bg-pink-100 text-pink-700' },
    { key: 'other',  title: 'अन्य', box: 'border-violet-100 bg-violet-50/40', pill: 'bg-violet-100 text-violet-700' },
  ];
  const groupOf = g => (g === 'male' || g === 'female') ? g : 'other';
  let parentId = null, data = null;

  const input = 'w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-800 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-100';

  function row(c) {
    const orderOpts = '<option value="">—</option>' + Array.from({ length: MAX }, (_, i) => i + 1)
      .map(n => `<option value="${n}" ${Number(c.birth_order || c.rank) === n ? 'selected' : ''}>${np(n)}</option>`).join('');
    const sel = (name, opts, cur) => `<select data-f="${name}" class="${input}">${Object.entries(opts).map(([k, v]) => `<option value="${k}" ${cur === k ? 'selected' : ''}>${v}</option>`).join('')}</select>`;
    return `<div data-child="${c.id}" class="grid grid-cols-[64px_1fr_auto] sm:grid-cols-[70px_minmax(0,1fr)_120px_80px_120px_auto] items-center gap-2 rounded-xl border border-slate-200 bg-white px-2.5 py-2">
      <label class="block"><span class="sr-only">सन्तान क्रम</span><select data-f="birth_order" class="${input} font-bold text-center">${orderOpts}</select></label>
      <div class="min-w-0">
        <div class="truncate text-sm font-semibold text-slate-800">${esc(c.display_name)}</div>
        <div class="truncate text-[11px] text-slate-400">${esc(c.member_no || '#' + c.id)}${c.display_name_np ? ' · ' + esc(c.display_name_np) : ''}${c.spouses?.length ? ' · 💍 ' + esc(c.spouses.join(', ')) : ''}</div>
      </div>
      <button type="button" data-marry="${c.id}" title="विवाह थप्नुहोस्"
        class="sm:order-last inline-flex h-8 items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2 text-xs font-semibold text-rose-700 hover:bg-rose-100">💍<span class="hidden md:inline">विवाह</span></button>
      <div class="col-span-3 grid grid-cols-3 gap-2 sm:contents">
        <label class="block"><span class="block text-[10px] font-semibold text-slate-400 sm:hidden">लिङ्ग</span>${sel('gender', { male: 'छोरा (M)', female: 'छोरी (F)', other: 'अन्य', unknown: 'थाहा छैन' }, c.gender)}</label>
        <label class="block"><span class="block text-[10px] font-semibold text-slate-400 sm:hidden">पुस्ता</span><input data-f="pusta" value="${esc(c.pusta)}" placeholder="${esc(data.parent.next_pusta || '')}" class="${input}"></label>
        <label class="block"><span class="block text-[10px] font-semibold text-slate-400 sm:hidden">सम्बन्ध</span>${sel('relation_type', { birth: 'Birth', adoption: 'Adoption', step: 'Step', guardianship: 'Guardianship' }, c.relation_type)}</label>
      </div>
    </div>`;
  }

  function render() {
    $('title').textContent = data.parent.display_name;
    $('meta').textContent = [data.parent.member_no, data.parent.pusta ? 'पु.' + data.parent.pusta : null, `${np(data.children.length)} सन्तान`].filter(Boolean).join(' · ');
    if (!data.children.length) { body.innerHTML = '<div class="py-8 text-center text-sm text-slate-400">कुनै सन्तान छैन।</div>'; return; }

    const head = `<div class="hidden sm:grid grid-cols-[70px_minmax(0,1fr)_120px_80px_120px_auto] gap-2 px-2.5 pb-1 text-[10px] font-bold uppercase tracking-wide text-slate-400">
      <span>क्रम</span><span>नाम</span><span>लिङ्ग</span><span>पुस्ता</span><span>सम्बन्ध</span><span class="w-8 md:w-[70px]"></span></div>`;
    body.innerHTML = GROUPS.map(g => {
      const kids = data.children.filter(c => groupOf(c.gender) === g.key);
      if (!kids.length) return '';
      return `<section class="mb-4 rounded-xl border ${g.box} p-2 sm:p-3">
        <div class="mb-2 flex items-center gap-2"><span class="rounded-full px-2.5 py-0.5 text-xs font-bold ${g.pill}">${g.title} (${np(kids.length)})</span></div>
        ${head}<div class="space-y-1.5">${kids.map(row).join('')}</div>
      </section>`;
    }).join('');
  }

  async function open(id) {
    parentId = id;
    data = null;
    body.innerHTML = '<div class="py-8 text-center text-sm text-slate-400">Loading…</div>';
    $('title').textContent = '…'; $('meta').textContent = '';
    modal.classList.remove('hidden'); modal.classList.add('flex');
    try {
      const res = await fetch(`${baseUrl}/${id}/children`, { headers: { 'Accept': 'application/json' } });
      data = await res.json();
      render();
    } catch (e) {
      body.innerHTML = '<div class="py-8 text-center text-sm text-rose-500">लोड गर्न सकिएन।</div>';
    }
  }

  function close() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

  async function save() {
    if (!data) return;
    const children = [...body.querySelectorAll('[data-child]')].map(el => {
      const v = f => el.querySelector(`[data-f="${f}"]`).value;
      return { id: +el.dataset.child, birth_order: v('birth_order') ? +v('birth_order') : null, gender: v('gender'), pusta: v('pusta'), relation_type: v('relation_type') };
    });
    saveBtn.disabled = true; saveBtn.textContent = 'सेभ हुँदैछ…';
    try {
      const res = await fetch(`${baseUrl}/${parentId}/children`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ children }),
      });
      const out = await res.json().catch(() => ({}));
      if (!res.ok) throw new Error(out.message || 'सेभ गर्न सकिएन।');
      data = out; render();
      status.textContent = out.message; status.className = 'text-[11px] font-semibold text-green-700';
      setTimeout(() => location.reload(), 700); // refresh the list so pusta / chips update
    } catch (e) {
      status.textContent = e.message; status.className = 'text-[11px] font-semibold text-rose-600';
    } finally {
      saveBtn.disabled = false; saveBtn.textContent = 'सेभ गर्नुहोस्';
    }
  }

  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-edit-children]');
    if (btn) { e.preventDefault(); open(btn.dataset.editChildren); }
    const marry = e.target.closest('[data-marry]');
    if (marry && data) {
      const c = data.children.find(x => String(x.id) === marry.dataset.marry);
      if (c && window.openMarriageForm) window.openMarriageForm(c);
    }
  });
  modal.querySelectorAll('[data-ce="close"]').forEach(b => b.addEventListener('click', close));
  modal.addEventListener('click', e => { if (e.target === modal) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });
  saveBtn.addEventListener('click', save);
})();

(() => {
  const searchUrl = @json(route('admin.persons.search'));
  const timers = new WeakMap();
  const controllers = new WeakMap();
  const searchCache = new Map();
  let recentPeople = [];
  let recentLoaded = false;

  // ids hidden from each picker (the other side's person + their ancestors/descendants)
  const blockedUrl = @json(route('admin.relationships.blocked'));
  const blocked = { parent_id: new Set(), child_id: new Set() };
  const otherSide = { parent_id: 'child_id', child_id: 'parent_id' };
  async function refreshBlocked(changedTarget) {
    const id = document.getElementById(changedTarget).value;
    const target = otherSide[changedTarget];
    if (!id) { blocked[target] = new Set(); return; }
    const as = changedTarget === 'parent_id' ? 'parent' : 'child';
    try {
      const res = await fetch(`${blockedUrl}?person_id=${encodeURIComponent(id)}&as=${as}`, { headers: { 'Accept': 'application/json' } });
      const ids = await res.json();
      blocked[target] = new Set((Array.isArray(ids) ? ids : [id]).map(String));
    } catch (e) {
      blocked[target] = new Set([String(id)]);
    }
  }

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
    const target = wrap.dataset.target;
    refreshBlocked(target).then(() => {
      const other = otherSide[target];
      const otherHidden = document.getElementById(other);
      if (otherHidden.value && blocked[other].has(String(otherHidden.value))) {
        otherHidden.value = '';
        const otherWrap = document.querySelector(`.person-search[data-person-picker="${other}"]`)?.closest('.relative');
        const otherSel = otherWrap?.querySelector('.person-selected');
        if (otherSel) {
          otherSel.innerHTML = '<span class="text-rose-600 font-semibold">पहिले छानिएको व्यक्ति यो सम्बन्धमा मिल्दैन — फेरि छान्नुहोस्।</span>';
          otherSel.className = 'person-selected mt-1 rounded-lg border border-rose-200 bg-rose-50 px-2 py-1 text-xs';
        }
      }
      document.dispatchEvent(new Event('relationship-picker-change'));
    });
    selected.querySelector('[data-clear]').addEventListener('click', () => {
      hidden.value = '';
      selected.classList.add('hidden');
      blocked[otherSide[target]] = new Set();
      document.dispatchEvent(new Event('relationship-picker-change'));
      input.focus();
      showRecent(wrap);
    });
  }
  function renderPeople(wrap, people, q = '', recent = false) {
    const results = wrap.querySelector('.person-results');
    const hide = blocked[wrap.dataset.target] || new Set();
    const hiddenCount = people.filter(p => hide.has(String(p.id))).length;
    people = people.filter(p => !hide.has(String(p.id)));
    const note = hiddenCount
      ? `<div class="px-3 py-1.5 text-[11px] text-amber-700 bg-amber-50 border-b">${hiddenCount} जना लुकाइयो — ${wrap.dataset.target === 'child_id' ? 'अभिभावक आफैं वा उहाँका पुर्खा' : 'सन्तान आफैं वा उहाँका सन्तान'} छान्न मिल्दैन।</div>`
      : '';
    if (!people.length) {
      results.innerHTML = note + '<div class="px-3 py-2 text-xs text-slate-400">No person found. Try ID, name, member no, or pusta.</div>';
      results.classList.remove('hidden');
      return;
    }
    results.innerHTML = note + `${recent ? '<div class="px-3 py-1.5 text-[11px] font-semibold text-slate-400 bg-slate-50 border-b">Recently added</div>' : ''}` + people.map(p => `
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

  // Pre-selected people (?child_id=… from the tree page, or the last submitted form)
  const prefill = @json($prefill ?? []);
  for (const [target, person] of Object.entries(prefill)) {
    const wrap = document.querySelector(`.person-search[data-person-picker="${target}"]`)?.closest('.relative');
    if (wrap) { wrap.dataset.target = target; selectPerson(wrap, person); }
  }
  if (prefill.child_id && !prefill.parent_id) {
    const form = document.getElementById('relationshipAddForm');
    if (form) form.style.scrollMarginTop = '140px'; // keep the hint banner visible below the sticky header
    form?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    form?.classList.add('ring-2', 'ring-amber-300');
    setTimeout(() => document.querySelector('.person-search[data-person-picker="parent_id"]')?.focus({ preventScroll: true }), 400);
  }
})();

(() => {
  const box = document.getElementById('relationshipPreview');
  const addBtn = document.getElementById('relationshipAddBtn');
  const previewUrl = @json(route('admin.relationships.preview'));
  const oldOrder = @json(old('birth_order'));
  let controller = null;
  let data = null;
  let chosen = null;

  function esc(v) {
    return String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
  }
  const npDigits = n => String(n ?? '').replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
  const TONE = {
    male:    { chip: 'bg-blue-50 text-blue-700 border-blue-200', num: 'bg-blue-600', title: 'छोरा' },
    female:  { chip: 'bg-pink-50 text-pink-700 border-pink-200', num: 'bg-pink-500', title: 'छोरी' },
    unknown: { chip: 'bg-violet-50 text-violet-700 border-violet-200', num: 'bg-violet-600', title: 'सन्तान' },
  };
  const tone = g => TONE[g] || TONE.unknown;
  function setSteps() {
    const parent = !!document.getElementById('parent_id').value;
    const child = !!document.getElementById('child_id').value;
    const done = [parent, child, child && !!chosen, false];
    const current = !parent ? 1 : !child ? 2 : 4;
    document.querySelectorAll('#relSteps .rel-step').forEach(li => {
      const n = +li.dataset.step;
      const num = li.querySelector('.rel-step-num');
      li.className = 'rel-step flex items-center gap-1.5 rounded-full border px-2.5 py-1 ' +
        (done[n - 1] ? 'border-green-200 bg-green-50 text-green-700'
          : n === current ? 'border-blue-200 bg-blue-50 text-blue-700'
          : 'border-slate-200 bg-white text-slate-400');
      num.className = 'rel-step-num flex h-4 w-4 items-center justify-center rounded-full text-[10px] text-white ' +
        (done[n - 1] ? 'bg-green-500' : n === current ? 'bg-blue-600' : 'bg-slate-200');
      num.textContent = done[n - 1] ? '✓' : n;
    });
    addBtn.disabled = !(parent && child);
  }

  // Sibling column for one gender, with the new child placed at the chosen number
  function column(g) {
    const t = tone(g);
    const rows = (data.siblings || [])
      .filter(r => (TONE[r.gender] ? r.gender : 'unknown') === g)
      .map(r => ({ name: r.display_name, rank: r.birth?.rank, isNew: false }));
    if (data.child && data.child.gender === g && chosen) {
      rows.push({ name: data.child.display_name, rank: chosen, isNew: true });
    }
    rows.sort((a, b) => (a.rank ?? 99) - (b.rank ?? 99));

    return `<div class="min-w-0 rounded-lg border border-slate-200 bg-white p-2.5">
      <div class="mb-2 inline-flex rounded-full border px-2 py-0.5 text-[11px] font-bold ${t.chip}">${t.title} (${npDigits(rows.length)})</div>
      <div class="space-y-1">
        ${rows.length ? rows.map(r => {
          return `<div class="flex items-center gap-2 rounded-md px-2 py-1 text-xs ${r.isNew ? 'bg-yellow-50 ring-1 ring-yellow-300' : ''}">
            <span class="flex-none w-5 h-5 rounded-full ${t.num} text-white text-[10px] font-bold flex items-center justify-center">${npDigits(r.rank)}</span>
            <span class="min-w-0 flex-1 truncate ${r.isNew ? 'font-bold text-slate-900' : 'font-medium text-slate-700'}">${esc(r.name)}</span>
            ${r.isNew ? '<span class="flex-none rounded bg-yellow-400 px-1 text-[10px] font-bold text-yellow-900">नयाँ</span>' : ''}
          </div>`;
        }).join('') : '<div class="px-2 text-xs text-slate-400">—</div>'}
      </div>
    </div>`;
  }

  function render() {
    setSteps();
    if (!data?.parent) { box.classList.add('hidden'); box.innerHTML = ''; return; }
    const c = data.child;
    const genders = ['male', 'female'];
    if ((data.siblings || []).some(r => !TONE[r.gender] || r.gender === 'unknown') || c?.gender === 'unknown') genders.push('unknown');

    let top;
    if (c) {
      const t = tone(c.gender);
      const opts = c.options.map(o => `<option value="${o.value}" ${o.value === chosen ? 'selected' : ''}>${esc(o.label)}</option>`).join('');
      top = `<div class="flex flex-col md:flex-row md:items-end gap-4">
        <div class="min-w-0 flex-1">
          <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">चरण ३ · जन्म क्रम</div>
          <div class="mt-1 flex flex-wrap items-center gap-2">
            <span class="text-base font-bold text-slate-900">${esc(c.display_name)}</span>
            <span class="rounded-full border px-2 py-0.5 text-[11px] font-bold ${t.chip}">${t.title}</span>
            ${c.pusta_will_be
              ? `<span class="rounded-full border border-amber-200 bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700">पुस्ता खाली → पु.${esc(c.pusta_will_be)} राखिनेछ</span>`
              : (c.pusta ? `<span class="rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] text-slate-600">पु.${esc(c.pusta)}</span>` : '')}
          </div>
          <div class="mt-1 text-xs text-slate-500">
            <span class="font-semibold text-slate-700">${esc(data.parent.display_name)}</span> को
            <span class="font-bold ${c.gender === 'female' ? 'text-pink-600' : c.gender === 'male' ? 'text-blue-600' : 'text-violet-600'}">
              ${chosen ? `${t.title} ${npDigits(chosen)}` : '—'}</span>
          </div>
        </div>
        <div class="w-full md:w-64">
          <label class="text-xs font-semibold text-slate-600">कतिऔं ${t.title}? *</label>
          <select name="birth_order" id="birthOrderSelect" required
            class="mt-1 w-full rounded-lg border-2 border-blue-300 bg-white px-3 py-2 text-sm font-semibold text-slate-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            ${opts}
          </select>
          <div class="mt-1 text-[11px] text-slate-400">१ देखि १० मध्ये छान्नुहोस्।</div>
        </div>
      </div>`;
    } else {
      top = `<div class="text-xs text-slate-500">
        <span class="font-semibold text-slate-800">${esc(data.parent.display_name)}</span>
        ${data.parent.pusta ? `(पु.${esc(data.parent.pusta)})` : ''} का हालका सन्तान — अब सन्तान छान्नुहोस्।
      </div>`;
    }

    box.innerHTML = top + `<div class="mt-4 grid grid-cols-1 ${genders.length === 3 ? 'sm:grid-cols-3' : 'sm:grid-cols-2'} gap-3">
      ${genders.map(column).join('')}
    </div>`;
    box.classList.remove('hidden');

    document.getElementById('birthOrderSelect')?.addEventListener('change', e => {
      chosen = parseInt(e.target.value, 10) || null;
      render();
    });
  }

  async function update() {
    const parentId = document.getElementById('parent_id').value;
    const childId = document.getElementById('child_id').value;
    if (!parentId) { data = null; chosen = null; render(); return; }

    controller?.abort();
    controller = new AbortController();
    const params = new URLSearchParams({ parent_id: parentId });
    if (childId) params.set('child_id', childId);
    try {
      const res = await fetch(`${previewUrl}?${params}`, { signal: controller.signal, headers: { 'Accept': 'application/json' } });
      data = await res.json();
      const values = (data?.child?.options || []).map(o => o.value);
      const keep = [chosen, parseInt(oldOrder, 10)].find(v => values.includes(v));
      chosen = data?.child ? (keep ?? data.child.default ?? null) : null;
      render();
    } catch (e) {
      if (e.name !== 'AbortError') { data = null; render(); }
    }
  }

  document.addEventListener('relationship-picker-change', update);
  update();
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
          ${parent.id ? `<button type="button" data-edit-children="${parent.id}" class="mt-2 inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">✎ सम्पादन / क्रम</button>` : ''}
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
        <div class="flex items-start gap-2">
          <div class="min-w-0 flex-1">
            <div class="font-semibold text-slate-800">${highlight(parentName, q)}</div>
            <div class="text-xs text-slate-400 mt-0.5">
              ${parent.member_no ? highlight('#' + parent.member_no, q) : ''}
              ${parent.pusta ? ` • ${highlight('पु.' + parent.pusta, q)}` : ''}
            </div>
          </div>
          ${parent.id ? `<button type="button" data-edit-children="${parent.id}" class="shrink-0 inline-flex items-center gap-1 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">✎ सम्पादन</button>` : ''}
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
@include('partials.marriage-form', ['marriagePerson' => null, 'returnBack' => true])
@include('partials.photo-camera')
@endsection
