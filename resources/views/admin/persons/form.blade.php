@php $p = $person ?? null; @endphp

<style>
  .death-wrap { grid-column: 1 / -1; }
  .death-panel { display: none; }
  .death-panel.is-open { display: block; }
</style>

<div class="grid grid-cols-1 md:grid-cols-4 gap-3">

  {{-- BASIC --}}
  <div class="md:col-span-2">
    <label class="text-xs text-slate-600">Display Name *</label>
    <input type="text" name="display_name" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('display_name', $p->display_name ?? '') }}" required>
  </div>

  <div>
    <label class="text-xs text-slate-600">नेपाली नाम</label>
    <input type="text" name="display_name_np" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('display_name_np', $p->display_name_np ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">Member No.</label>
    <input type="text" name="member_no" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('member_no', $p->member_no ?? '') }}" placeholder="DLUMP01">
  </div>

  <div>
    <label class="text-xs text-slate-600">Given Name *</label>
    <input type="text" name="given_name" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('given_name', $p->given_name ?? '') }}" required>
  </div>

  <div>
    <label class="text-xs text-slate-600">Middle Name</label>
    <input type="text" name="middle_name" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('middle_name', $p->middle_name ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">Family Name</label>
    <input type="text" name="family_name" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('family_name', $p->family_name ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">Gender *</label>
    <select name="gender" class="border rounded-lg px-3 py-2 w-full">
      @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
        <option value="{{ $k }}" @selected(old('gender', $p->gender ?? 'unknown') === $k)>{{ $v }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="text-xs text-slate-600">Photo Path</label>
    <input type="text" name="photo_path" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('photo_path', $p->photo_path ?? '') }}" placeholder="photos/ram.jpg">
  </div>

  <div>
    <label class="text-xs text-slate-600">पुस्ता</label>
    <input type="text" name="pusta" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('pusta', $p->pusta ?? '') }}" placeholder="1">
  </div>

  <div>
    <label class="text-xs text-slate-600">सदस्यको प्रकार</label>
    <input type="text" name="member_type" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('member_type', $p->member_type ?? '') }}" placeholder="दाजु भाई">
  </div>

  <div>
    <label class="text-xs text-slate-600">सदस्यता</label>
    <input type="text" name="membership" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('membership', $p->membership ?? '') }}">
  </div>

  {{-- DATES --}}
  <div>
    <label class="text-xs text-slate-600">Birth Date (A.D.)</label>
    <input type="date" name="birth_date" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('birth_date', isset($p->birth_date) ? $p->birth_date->format('Y-m-d') : '') }}">
  </div>

  {{-- CONTACT --}}
  <div class="md:col-span-2">
    <label class="text-xs text-slate-600">जन्मस्थान</label>
    <input type="text" name="birth_place" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('birth_place', $p->birth_place ?? '') }}" placeholder="..., Nepal">
  </div>

  <div class="md:col-span-2">
    <label class="text-xs text-slate-600">घरको ठेगाना</label>
    <input type="text" name="address" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('address', $p->address ?? '') }}" placeholder="Address not provided">
  </div>

  <div>
    <label class="text-xs text-slate-600">मोबाइल नम्बर</label>
    <input type="text" name="mobile" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('mobile', $p->mobile ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">Email ID</label>
    <input type="email" name="email" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('email', $p->email ?? '') }}">
  </div>

  {{-- EDUCATION / JOB --}}
  <div>
    <label class="text-xs text-slate-600">शिक्षा</label>
    <input type="text" name="education" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('education', $p->education ?? '') }}" placeholder="थाहा छैन">
  </div>

  <div>
    <label class="text-xs text-slate-600">पेशा</label>
    <input type="text" name="occupation" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('occupation', $p->occupation ?? '') }}">
  </div>

  {{-- EXTRA --}}
  <div>
    <label class="text-xs text-slate-600">Lineage</label>
    <input type="text" name="lineage" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('lineage', $p->lineage ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">परिवारको प्रकार</label>
    <input type="text" name="family_type" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('family_type', $p->family_type ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">रक्त समूह</label>
    <input type="text" name="blood_group" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('blood_group', $p->blood_group ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">राशिफल</label>
    <input type="text" name="rashifal" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('rashifal', $p->rashifal ?? '') }}">
  </div>

  <div>
    <label class="text-xs text-slate-600">धर्म</label>
    <input type="text" name="religion" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('religion', $p->religion ?? '') }}">
  </div>

  <div class="md:col-span-2">
    <label class="text-xs text-slate-600">विशेष नोट</label>
    <textarea name="special_note" rows="3" class="border rounded-lg px-3 py-2 w-full">{{ old('special_note', $p->special_note ?? '') }}</textarea>
  </div>

  <div>
    <label class="text-xs text-slate-600">Registered By</label>
    <input type="text" name="registered_by" class="border rounded-lg px-3 py-2 w-full"
      value="{{ old('registered_by', $p->registered_by ?? '') }}" placeholder="Self">
  </div>

  {{-- BIO --}}
  <div class="md:col-span-4">
    <label class="text-xs text-slate-600">Bio</label>
    <textarea name="bio" rows="4" class="border rounded-lg px-3 py-2 w-full" placeholder="जीवनी...">{{ old('bio', $p->bio ?? '') }}</textarea>
  </div>

  {{-- ✅ DECEASED CHECKBOX (LAST) --}}
  <div class="death-wrap">
    <div class="flex items-center justify-between border rounded-2xl px-4 py-3 bg-slate-50">
      <div class="flex items-center gap-2">
        <input
          type="checkbox"
          name="is_deceased"
          value="1"
          id="is_deceased"
          @checked(old('is_deceased', $p->is_deceased ?? false))
        >
        <label for="is_deceased" class="text-sm font-semibold text-slate-700">
          Deceased (मृत्यु भएको)
        </label>
      </div>

      <button type="button" id="deathToggleBtn"
        class="text-xs px-3 py-1 rounded-lg border bg-white hover:bg-slate-50">
        Show details
      </button>
    </div>

    {{-- ✅ COLLAPSIBLE DEATH PANEL --}}
    <div id="deathPanel" class="death-panel mt-3 border rounded-2xl p-4 bg-red-50/30">
      <div class="flex items-center justify-between mb-3">
        <div class="font-semibold text-slate-800">Death Details</div>
        <button type="button" id="deathCollapseBtn" class="text-xs px-3 py-1 rounded-lg border bg-white hover:bg-slate-50">
          Collapse
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
          <label class="text-xs text-slate-600">Death Date (A.D.)</label>
          <input id="death_date" type="date" name="death_date" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('death_date', isset($p->death_date) ? $p->death_date->format('Y-m-d') : '') }}">
        </div>

        <div class="md:col-span-2">
          <label class="text-xs text-slate-600">मृत्यु स्थान</label>
          <input id="death_place" type="text" name="death_place" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('death_place', $p->death_place ?? '') }}">
        </div>

        <div>
          <label class="text-xs text-slate-600">मृत्यु तिथी</label>
          <input id="death_tithi" type="text" name="death_tithi" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('death_tithi', $p->death_tithi ?? '') }}">
        </div>

        <div class="md:col-span-4">
          <label class="text-xs text-slate-600">मृत्युको कारण</label>
          <input id="death_reason" type="text" name="death_reason" class="border rounded-lg px-3 py-2 w-full"
            value="{{ old('death_reason', $p->death_reason ?? '') }}">
        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function () {
  const chk = document.getElementById('is_deceased');
  const panel = document.getElementById('deathPanel');
  const btn = document.getElementById('deathToggleBtn');
  const collapseBtn = document.getElementById('deathCollapseBtn');

  const fields = ['death_date','death_place','death_tithi','death_reason'].map(id => document.getElementById(id));

  function setOpen(open) {
    if (open) {
      panel.classList.add('is-open');
      btn.textContent = 'Hide details';
    } else {
      panel.classList.remove('is-open');
      btn.textContent = 'Show details';
    }
  }

  function clearFields() {
    fields.forEach(el => { if (el) el.value = ''; });
  }

  // initial state (edit page)
  setOpen(!!chk.checked);

  // checkbox toggles open/close + clears on uncheck
  chk.addEventListener('change', function () {
    if (chk.checked) {
      setOpen(true);
    } else {
      setOpen(false);
      clearFields();
    }
  });

  // button toggles panel (but keep checkbox logic consistent)
  btn.addEventListener('click', function () {
    const open = !panel.classList.contains('is-open');
    setOpen(open);
    chk.checked = open;
    if (!open) clearFields();
  });

  // collapse button
  collapseBtn.addEventListener('click', function () {
    setOpen(false);
    chk.checked = false;
    clearFields();
  });
})();
</script>
