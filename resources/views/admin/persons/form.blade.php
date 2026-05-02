@php
/** @var \App\Models\Person|null $p */
$p = $person ?? null;
@endphp

{{-- ════════════════════════════════════════
    SECTION HEADER MACRO
════════════════════════════════════════ --}}
@php
function secHead(string $icon, string $title, string $color = 'blue'): string {
    return <<<HTML
<div class="md:col-span-4 flex items-center gap-2 mt-2 mb-1">
  <span class="text-{$color}-500 text-base">{$icon}</span>
  <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{$title}</span>
  <div class="flex-1 border-t border-slate-200"></div>
</div>
HTML;
}
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-3" id="personFormGrid">

  {{-- ── BASIC INFO ─────────────────────────────── --}}
  <div class="md:col-span-4 flex items-center gap-2 mt-1 mb-0">
    <span class="text-blue-500">👤</span>
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Basic Information</span>
    <div class="flex-1 border-t border-slate-200"></div>
  </div>

  <div class="md:col-span-2">
    <label class="form-label">नेपाली नाम <span class="text-red-500">*</span></label>
    <input type="text" name="display_name" class="form-input"
      value="{{ old('display_name', $p->display_name ?? '') }}" required placeholder="नेपाली नाम">
    @error('display_name')<p class="form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="form-label">Full Name (English)</label>
    <input type="text" name="display_name_np" class="form-input"
      value="{{ old('display_name_np', $p->display_name_np ?? '') }}" placeholder="Full English name">
  </div>

  <div>
    <label class="form-label">Limbu Name / ᤕᤰᤌᤢᤱ नाम</label>
    <input type="text" name="display_name_limbu" class="form-input"
      value="{{ old('display_name_limbu', $p->display_name_limbu ?? '') }}" placeholder="Limbu script name">
  </div>

  <div>
    <label class="form-label">Member No. / सदस्यता नम्बर</label>
    <input type="text" name="member_no" class="form-input"
      value="{{ old('member_no', $p->member_no ?? ($nextMemberNo ?? 'Auto generated')) }}"
      readonly
      placeholder="Auto generated">
    <p class="text-[11px] text-slate-400 mt-1">System generated automatically from M-1000.</p>
  </div>

  <div>
    <label class="form-label">Gender <span class="text-red-500">*</span></label>
    <select name="gender" class="form-input">
      @foreach (['male' => 'Male ♂', 'female' => 'Female ♀', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
        <option value="{{ $k }}" @selected(old('gender', $p->gender ?? 'unknown') === $k)>{{ $v }}</option>
      @endforeach
    </select>
    @error('gender')<p class="form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="form-label">पुस्ता (Generation)</label>
    <input type="text" name="pusta" class="form-input"
      value="{{ old('pusta', $p->pusta ?? '') }}" placeholder="e.g. 5">
  </div>

  <div>
    <label class="form-label">सदस्यको प्रकार</label>
    <input type="text" name="member_type" class="form-input"
      value="{{ old('member_type', $p->member_type ?? '') }}" placeholder="दाजु भाई">
  </div>

  <div>
    <label class="form-label">सदस्यता</label>
    <input type="text" name="membership" class="form-input"
      value="{{ old('membership', $p->membership ?? '') }}">
  </div>

  {{-- Photo upload --}}
  <div class="md:col-span-2">
    <label class="form-label">Photo (max 200 KB · JPEG / PNG / WebP)</label>
    <div class="flex items-start gap-3">
      {{-- Current / preview image --}}
      <div id="photoThumb"
        class="w-16 h-16 rounded-full bg-slate-100 border-2 border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center text-2xl">
        @if(!empty($p?->photo_path) && file_exists(public_path($p->photo_path)))
          <img src="{{ asset($p->photo_path) }}" class="w-full h-full object-cover" alt="photo" id="photoPreviewImg">
        @else
          <span id="photoPreviewImg">📷</span>
        @endif
      </div>
      <div class="flex-1">
        <input type="file" name="photo" id="photoFileInput"
          accept="image/jpeg,image/jpg,image/png,image/webp"
          class="form-input text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
        <input type="hidden" name="photo_path" value="{{ old('photo_path', $p->photo_path ?? '') }}">
        <p class="text-[11px] text-slate-400 mt-1">
          Leave empty to keep existing photo.
          <span id="photoSizeErr" class="text-red-500 font-medium hidden">⚠ File exceeds 200 KB — please choose a smaller image.</span>
        </p>
        @error('photo')<p class="form-error">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

  {{-- ── DATES ───────────────────────────────────── --}}
  <div class="md:col-span-4 flex items-center gap-2 mt-2 mb-0">
    <span class="text-green-500">📅</span>
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Dates &amp; Place of Birth</span>
    <div class="flex-1 border-t border-slate-200"></div>
  </div>

  <div>
    <label class="form-label">Birth Date (A.D.)</label>
    <input type="date" name="birth_date" class="form-input"
      value="{{ old('birth_date', isset($p->birth_date) ? $p->birth_date->format('Y-m-d') : '') }}">
  </div>

  <div>
    <label class="form-label">Birth Date (B.S.)</label>
    <input type="text" name="birth_date_bs" class="form-input"
      placeholder="2050-05-12"
      value="{{ old('birth_date_bs', $p->birth_date_bs ?? '') }}">
  </div>

  <div class="md:col-span-2">
    <label class="form-label">जन्मस्थान</label>
    <input type="text" name="birth_place" class="form-input"
      value="{{ old('birth_place', $p->birth_place ?? '') }}" placeholder="Village / District, Nepal">
  </div>

  {{-- ── CONTACT ─────────────────────────────────── --}}
  <div class="md:col-span-4 flex items-center gap-2 mt-2 mb-0">
    <span class="text-purple-500">📞</span>
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Contact &amp; Address</span>
    <div class="flex-1 border-t border-slate-200"></div>
  </div>

  <div class="md:col-span-2">
    <label class="form-label">घरको ठेगाना</label>
    <input type="text" name="address" class="form-input"
      value="{{ old('address', $p->address ?? '') }}" placeholder="Current address">
  </div>

  <div>
    <label class="form-label">मोबाइल नम्बर</label>
    <input type="text" name="mobile" class="form-input"
      value="{{ old('mobile', $p->mobile ?? '') }}" placeholder="+977-9800000000">
  </div>

  <div>
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-input"
      value="{{ old('email', $p->email ?? '') }}">
    @error('email')<p class="form-error">{{ $message }}</p>@enderror
  </div>

  {{-- ── EDUCATION / JOB ──────────────────────────── --}}
  <div class="md:col-span-4 flex items-center gap-2 mt-2 mb-0">
    <span class="text-yellow-500">🎓</span>
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Education &amp; Occupation</span>
    <div class="flex-1 border-t border-slate-200"></div>
  </div>

  <div class="md:col-span-2">
    <label class="form-label">शिक्षा</label>
    <input type="text" name="education" class="form-input"
      value="{{ old('education', $p->education ?? '') }}" placeholder="e.g. BA, Masters...">
  </div>

  <div class="md:col-span-2">
    <label class="form-label">पेशा / Occupation</label>
    <input type="text" name="occupation" class="form-input"
      value="{{ old('occupation', $p->occupation ?? '') }}" placeholder="Farmer, Teacher...">
  </div>

  {{-- ── PERSONAL DETAILS ─────────────────────────── --}}
  <div class="md:col-span-4 flex items-center gap-2 mt-2 mb-0">
    <span class="text-orange-500">🧬</span>
    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Personal Details</span>
    <div class="flex-1 border-t border-slate-200"></div>
  </div>

  <div>
    <label class="form-label">Lineage</label>
    <input type="text" name="lineage" class="form-input"
      value="{{ old('lineage', $p->lineage ?? '') }}">
  </div>

  <div>
    <label class="form-label">परिवारको प्रकार</label>
    <input type="text" name="family_type" class="form-input"
      value="{{ old('family_type', $p->family_type ?? '') }}">
  </div>

  <div>
    <label class="form-label">रक्त समूह</label>
    <select name="blood_group" class="form-input">
      <option value="">— Select —</option>
      @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
        <option value="{{ $bg }}" @selected(old('blood_group',$p->blood_group??'') === $bg)>{{ $bg }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="form-label">राशिफल</label>
    <select name="rashifal" class="form-input">
      <option value="">— Select —</option>
      @foreach(['मेष','वृष','मिथुन','कर्कट','सिंह','कन्या','तुला','वृश्चिक','धनु','मकर','कुम्भ','मीन'] as $r)
        <option value="{{ $r }}" @selected(old('rashifal',$p->rashifal??'') === $r)>{{ $r }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="form-label">धर्म</label>
    <input type="text" name="religion" class="form-input"
      value="{{ old('religion', $p->religion ?? '') }}" placeholder="Hindu / Buddhist...">
  </div>

  <div>
    <label class="form-label">Registered By</label>
    <input type="text" name="registered_by" class="form-input"
      value="{{ old('registered_by', $p->registered_by ?? '') }}" placeholder="Self">
  </div>

  <div class="md:col-span-2">
    <label class="form-label">विशेष नोट</label>
    <textarea name="special_note" rows="2" class="form-input">{{ old('special_note', $p->special_note ?? '') }}</textarea>
  </div>

  {{-- ── BIO ─────────────────────────────────────── --}}
  <div class="md:col-span-4">
    <label class="form-label">Bio / जीवनी</label>
    <textarea name="bio" rows="3" class="form-input" placeholder="Short biography...">{{ old('bio', $p->bio ?? '') }}</textarea>
  </div>

  {{-- ════════════════════════════════════════════════
      MARRIAGE / UNION SECTION
  ════════════════════════════════════════════════ --}}
  <div class="md:col-span-4" id="unionWrap">
    {{-- Toggle bar --}}
    <div class="flex items-center justify-between border rounded-2xl px-4 py-3 bg-pink-50/60 border-pink-200">
      <div class="flex items-center gap-2">
        <input type="checkbox" name="add_union" value="1" id="add_union"
          class="w-4 h-4 accent-pink-500"
          @checked(old('add_union'))>
        <label for="add_union" class="text-sm font-semibold text-slate-700 cursor-pointer select-none">
          💍 Add Marriage / Union (विवाह थप्नुहोस्)
        </label>
      </div>
      <button type="button" id="unionToggleBtn"
        class="text-xs px-3 py-1 rounded-lg border border-pink-300 bg-white hover:bg-pink-50 text-pink-700">
        Show fields
      </button>
    </div>

    {{-- Collapsible panel --}}
    <div id="unionPanel" class="union-panel mt-3 border border-pink-200 rounded-2xl p-4 bg-pink-50/30" style="display:none">
      <p class="text-xs text-slate-500 mb-3">
        Search an existing spouse, or enter a new spouse name if they are not registered yet.
      </p>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">

        {{-- Spouse AJAX search --}}
        <div class="md:col-span-2 relative">
          <label class="form-label">Registered spouse search</label>
          <div class="relative">
            <input type="text" id="spouseSearchInput"
              class="form-input pr-8" autocomplete="off"
              placeholder="Type name in English or Nepali…">
            <span id="spouseSearchSpinner"
              class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">⏳</span>
          </div>
          <input type="hidden" name="union_spouse_id" id="union_spouse_id"
            value="{{ old('union_spouse_id') }}">

          {{-- Selected display --}}
          <div id="spouseSelected"
            class="mt-1.5 items-center gap-2 px-3 py-2 bg-green-50 border border-green-300 rounded-lg text-sm text-green-800"
            style="display:none">
            <span>✅</span>
            <span id="spouseSelectedName"></span>
            <button type="button" id="clearSpouseBtn" class="ml-auto text-xs text-red-500 hover:underline">Remove</button>
          </div>

          {{-- Dropdown results --}}
          <div id="spouseDropdown"
            class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto">
          </div>
        </div>

        <div class="md:col-span-2">
          <label class="form-label">New spouse full name (if not registered)</label>
          <input type="text" name="union_spouse_name" class="form-input"
            value="{{ old('union_spouse_name') }}"
            placeholder="Full name of spouse">
          @error('union_spouse_name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="form-label">नयाँ जीवनसाथी नेपाली नाम</label>
          <input type="text" name="union_spouse_name_np" class="form-input"
            value="{{ old('union_spouse_name_np') }}" placeholder="नेपाली नाम">
        </div>

        <div>
          <label class="form-label">Limbu name</label>
          <input type="text" name="union_spouse_name_limbu" class="form-input"
            value="{{ old('union_spouse_name_limbu') }}" placeholder="Limbu script name">
        </div>

        <div>
          <label class="form-label">New spouse gender</label>
          @php $defaultSpouseGender = old('union_spouse_gender', ($p->gender ?? '') === 'male' ? 'female' : (($p->gender ?? '') === 'female' ? 'male' : 'unknown')); @endphp
          <select name="union_spouse_gender" class="form-input">
            @foreach(['female' => 'Female / पत्नी', 'male' => 'Male / पति', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
              <option value="{{ $k }}" @selected($defaultSpouseGender === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="form-label">विवाह मिति (A.D.)</label>
          <input type="date" name="union_start_date" class="form-input"
            value="{{ old('union_start_date') }}">
        </div>

        <div>
          <label class="form-label">विवाह प्रकार</label>
          <select name="union_type" class="form-input">
            @foreach(['married' => '💍 Married','divorced' => '💔 Divorced','widowed' => '🕊️ Widowed','separated' => '🔗 Separated'] as $k => $v)
              <option value="{{ $k }}" @selected(old('union_type','married') === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="md:col-span-4">
          <label class="form-label">Notes (optional)</label>
          <input type="text" name="union_notes" class="form-input"
            value="{{ old('union_notes') }}" placeholder="Any additional notes about this union…">
        </div>

      </div>
    </div>
  </div>

  {{-- ════════════════════════════════════════════════
      DECEASED SECTION
  ════════════════════════════════════════════════ --}}
  <div class="md:col-span-4" id="deathWrap">
    <div class="flex items-center justify-between border rounded-2xl px-4 py-3 bg-slate-50">
      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_deceased" value="1" id="is_deceased"
          class="w-4 h-4"
          @checked(old('is_deceased', $p->is_deceased ?? false))>
        <label for="is_deceased" class="text-sm font-semibold text-slate-700 cursor-pointer select-none">
          🕯️ Deceased (मृत्यु भएको)
        </label>
      </div>
      <button type="button" id="deathToggleBtn"
        class="text-xs px-3 py-1 rounded-lg border bg-white hover:bg-slate-50">
        Show details
      </button>
    </div>

    <div id="deathPanel" class="mt-3 border rounded-2xl p-4 bg-red-50/30" style="display:none">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold text-slate-800 text-sm">Death Details</div>
        <button type="button" id="deathCollapseBtn" class="text-xs px-3 py-1 rounded-lg border bg-white hover:bg-slate-50">
          Collapse
        </button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="form-label">Death Date (A.D.)</label>
          <input id="death_date" type="date" name="death_date" class="form-input"
            value="{{ old('death_date', isset($p->death_date) ? $p->death_date->format('Y-m-d') : '') }}">
        </div>
        <div class="md:col-span-2">
          <label class="form-label">मृत्यु स्थान</label>
          <input id="death_place" type="text" name="death_place" class="form-input"
            value="{{ old('death_place', $p->death_place ?? '') }}">
        </div>
        <div>
          <label class="form-label">मृत्यु तिथि (B.S.)</label>
          <input id="death_tithi" type="text" name="death_tithi" class="form-input"
            value="{{ old('death_tithi', $p->death_tithi ?? '') }}" placeholder="2080-08-15">
        </div>
        <div class="md:col-span-4">
          <label class="form-label">मृत्युको कारण</label>
          <input id="death_reason" type="text" name="death_reason" class="form-input"
            value="{{ old('death_reason', $p->death_reason ?? '') }}">
        </div>
      </div>
    </div>
  </div>

</div>{{-- end grid --}}

<style>
.form-label { display:block; font-size:.7rem; color:#64748b; margin-bottom:.2rem; font-weight:500; }
.form-input  { width:100%; border:1px solid #cbd5e1; border-radius:.5rem; padding:.45rem .75rem; font-size:.875rem; background:#fff; transition:border-color .15s; }
.form-input:focus { outline:none; border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,.15); }
.form-error  { font-size:.7rem; color:#ef4444; margin-top:.2rem; }
</style>

<script>
(function () {
  /* ── Death panel ───────────────── */
  const deathChk = document.getElementById('is_deceased');
  const deathPanel = document.getElementById('deathPanel');
  const deathBtn   = document.getElementById('deathToggleBtn');
  const deathCollBtn = document.getElementById('deathCollapseBtn');
  const deathFields = ['death_date','death_place','death_tithi','death_reason'].map(id=>document.getElementById(id));

  function setDeathOpen(open) {
    deathPanel.style.display = open ? 'block' : 'none';
    deathBtn.textContent = open ? 'Hide details' : 'Show details';
  }
  function clearDeathFields() { deathFields.forEach(el=>{if(el) el.value='';}); }

  setDeathOpen(!!deathChk.checked);

  deathChk.addEventListener('change', () => {
    setDeathOpen(deathChk.checked);
    if (!deathChk.checked) clearDeathFields();
  });
  deathBtn.addEventListener('click', () => {
    const open = deathPanel.style.display === 'none';
    setDeathOpen(open);
    deathChk.checked = open;
    if (!open) clearDeathFields();
  });
  deathCollapseBtn && deathCollapseBtn.addEventListener('click', () => {
    setDeathOpen(false);
    deathChk.checked = false;
    clearDeathFields();
  });

  /* ── Union / Marriage panel ────── */
  const unionChk   = document.getElementById('add_union');
  const unionPanel = document.getElementById('unionPanel');
  const unionBtn   = document.getElementById('unionToggleBtn');

  function setUnionOpen(open) {
    unionPanel.style.display = open ? 'block' : 'none';
    unionBtn.textContent = open ? 'Hide fields' : 'Show fields';
  }

  setUnionOpen(!!unionChk.checked);

  unionChk.addEventListener('change', () => setUnionOpen(unionChk.checked));
  unionBtn.addEventListener('click', () => {
    const open = unionPanel.style.display === 'none';
    setUnionOpen(open);
    unionChk.checked = open;
  });

  /* ── Spouse AJAX autocomplete ──── */
  const spouseInput    = document.getElementById('spouseSearchInput');
  const spouseHidden   = document.getElementById('union_spouse_id');
  const spouseDropdown = document.getElementById('spouseDropdown');
  const spouseSelected = document.getElementById('spouseSelected');
  const spouseSelName  = document.getElementById('spouseSelectedName');
  const clearSpouseBtn = document.getElementById('clearSpouseBtn');
  const spouseSpinner  = document.getElementById('spouseSearchSpinner');
  const newSpouseInputs = Array.from(document.querySelectorAll('[name="union_spouse_name"], [name="union_spouse_name_np"], [name="union_spouse_name_limbu"]'));

  let debTimer = null;
  const searchUrl = '{{ route("admin.persons.search") }}';

  function genderIcon(g) {
    return g === 'male' ? '♂' : g === 'female' ? '♀' : '◌';
  }

  function selectSpouse(id, label) {
    spouseHidden.value = id;
    spouseSelName.textContent = label;
    spouseSelected.style.display = 'flex';
    spouseDropdown.classList.add('hidden');
    spouseInput.value = '';
    newSpouseInputs.forEach(input => input.value = '');
  }

  function clearSpouse() {
    spouseHidden.value = '';
    spouseSelected.style.display = 'none';
    spouseInput.value = '';
  }

  clearSpouseBtn && clearSpouseBtn.addEventListener('click', clearSpouse);
  newSpouseInputs.forEach(input => {
    input.addEventListener('input', () => {
      if (!input.value.trim()) return;
      clearSpouse();
    });
  });

  spouseInput && spouseInput.addEventListener('input', function () {
    clearTimeout(debTimer);
    const q = this.value.trim();
    if (!q) { spouseDropdown.classList.add('hidden'); return; }
    debTimer = setTimeout(async () => {
      spouseSpinner.classList.remove('hidden');
      try {
        const res = await fetch(`${searchUrl}?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        spouseSpinner.classList.add('hidden');
        if (!data.length) {
          spouseDropdown.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400">No results found</div>';
          spouseDropdown.classList.remove('hidden');
          return;
        }
        spouseDropdown.innerHTML = data.map(person => {
          const np = person.display_name_np ? ` / ${person.display_name_np}` : '';
          const limbu = person.display_name_limbu ? ` / ${person.display_name_limbu}` : '';
          const yr = person.birth_year ? ` (${person.birth_year})` : '';
          const tag = person.is_deceased ? '<span class="text-red-400 text-[10px] ml-1">✝</span>' : '';
          return `<button type="button"
            class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 border-b border-slate-100 last:border-0 text-sm"
            data-id="${person.id}"
            data-label="${person.display_name}${np}${limbu}${yr}">
            <span class="font-medium">${person.display_name}</span>
            ${np ? `<span class="text-slate-400 text-xs">${np}</span>` : ''}
            ${limbu ? `<span class="text-slate-400 text-xs">${limbu}</span>` : ''}
            <span class="text-xs text-slate-400 ml-1">${genderIcon(person.gender)}${yr}</span>
            ${person.member_no ? `<span class="text-xs text-indigo-400 ml-1">#${person.member_no}</span>` : ''}
            ${tag}
          </button>`;
        }).join('');
        spouseDropdown.classList.remove('hidden');

        spouseDropdown.querySelectorAll('button[data-id]').forEach(btn => {
          btn.addEventListener('click', () => selectSpouse(btn.dataset.id, btn.dataset.label));
        });
      } catch(e) {
        spouseSpinner.classList.add('hidden');
      }
    }, 280);
  });

  // Close dropdown on outside click
  document.addEventListener('click', e => {
    if (!spouseInput?.contains(e.target) && !spouseDropdown?.contains(e.target)) {
      spouseDropdown?.classList.add('hidden');
    }
  });

  // Restore state if old('union_spouse_id') exists (validation fail)
  @if(old('union_spouse_id'))
    spouseHidden.value = '{{ old("union_spouse_id") }}';
    spouseSelName.textContent = 'Previously selected (ID: {{ old("union_spouse_id") }})';
    spouseSelected.style.display = 'flex';
  @endif

  /* ── Photo preview ─────────────── */
  const photoInput = document.getElementById('photoFileInput');
  const photoThumb = document.getElementById('photoThumb');
  const photoErr   = document.getElementById('photoSizeErr');

  photoInput && photoInput.addEventListener('change', function () {
    const max = 200 * 1024;
    if (!this.files || !this.files[0]) return;
    const file = this.files[0];
    if (file.size > max) {
      photoErr.style.display = 'inline';
      this.value = '';
      return;
    }
    photoErr.style.display = 'none';
    const reader = new FileReader();
    reader.onload = (e) => {
      photoThumb.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-full" alt="preview">`;
    };
    reader.readAsDataURL(file);
  });

})();
</script>
