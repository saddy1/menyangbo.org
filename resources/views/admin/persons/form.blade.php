@php
/** @var \App\Models\Person|null $p */
$p = $person ?? null;
@endphp

{{-- ════════════════════════════════════════
    SECTION HEADER MACRO
════════════════════════════════════════ --}}
@php
if (!function_exists('secHead')) { // the view can render more than once per process (tests, queues)
function secHead(string $icon, string $title, string $color = 'blue'): string {
    return <<<HTML
<div class="md:col-span-4 flex items-center gap-2 mt-2 mb-1">
  <span class="text-{$color}-500 text-base">{$icon}</span>
  <span class="text-xs font-bold uppercase tracking-wider text-slate-500">{$title}</span>
  <div class="flex-1 border-t border-slate-200"></div>
</div>
HTML;
}
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
    <label class="form-label">Full Name (English) @unless($p)<span class="text-red-500">*</span>@endunless</label>
    <input type="text" name="display_name_np" class="form-input"
      value="{{ old('display_name_np', $p->display_name_np ?? '') }}" placeholder="Full English name" @unless($p) required @endunless>
    @error('display_name_np')<p class="form-error">{{ $message }}</p>@enderror
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
    <select name="gender" id="personGenderSelect" class="form-input">
      @foreach (['male' => 'Male ♂', 'female' => 'Female ♀', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
        <option value="{{ $k }}" @selected(old('gender', $p ? ($p->gender ?? 'unknown') : 'male') === $k)>{{ $v }}</option>
      @endforeach
    </select>
    @error('gender')<p class="form-error">{{ $message }}</p>@enderror
  </div>

  {{-- सन्तान क्रम: only when editing someone who is a child (has a parent) --}}
  @if(!empty($orderParent))
  <div>
    <label class="form-label">
      सन्तान क्रम (<span id="personOrderRelation">सन्तान</span>)
      <span class="font-normal text-slate-400">— {{ $orderParent->display_name }} को</span>
    </label>
    <select name="birth_order" id="personBirthOrder" class="form-input"
      data-current="{{ old('birth_order', $currentOrder ?? '') }}"></select>
    <p class="text-[11px] text-slate-400 mt-1" id="personOrderTaken"></p>
    @error('birth_order')<p class="form-error">{{ $message }}</p>@enderror
  </div>
  @endif

  <div>
    <label class="form-label">पुस्ता (Generation)</label>
    <input type="text" name="pusta" class="form-input"
      value="{{ old('pusta', $p->pusta ?? '') }}" placeholder="e.g. 5">
  </div>

  <div>
    <label class="form-label">सदस्यको प्रकार</label>
    @php $mt = old('member_type', $p ? ($p->member_type ?? '') : \App\Support\MemberType::forGender(old('gender', 'male'))); @endphp
    <select name="member_type" id="personMemberType" class="form-input">
      <option value="">—</option>
      @foreach(\App\Support\MemberType::all() as $type)
        <option value="{{ $type }}" @selected($mt === $type)>{{ $type }}</option>
      @endforeach
    </select>
    <p class="text-[11px] text-slate-400 mt-1">पुरुष → दाजुभाइ · महिला → दिदीबहिनी · विवाह गरेर आएकी → बुहारी</p>
    @error('member_type')<p class="form-error">{{ $message }}</p>@enderror
  </div>

  <div>
    <label class="form-label">सदस्यता</label>
    <input type="text" name="membership" class="form-input"
      value="{{ old('membership', $p->membership ?? '') }}">
  </div>

  {{-- Photo upload --}}
  <div class="md:col-span-2">
    <label class="form-label">Photo (max 500 KB · JPEG / PNG / WebP)</label>
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
        <input type="file" name="photo" id="photoFileInput" data-camera
          accept="image/jpeg,image/jpg,image/png,image/webp"
          class="form-input text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
        <input type="hidden" name="photo_path" value="{{ old('photo_path', $p->photo_path ?? '') }}">
        <p class="text-[11px] text-slate-400 mt-1">
          Leave empty to keep existing photo.
          <span id="photoSizeErr" class="text-red-500 font-medium hidden">⚠ File exceeds 500 KB — please choose a smaller image.</span>
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
    <div id="unionPanel" class="union-panel mt-3 space-y-4 rounded-2xl border border-pink-200 bg-white p-4" style="display:none">

      {{-- 1. existing spouse --}}
      <div class="relative">
        <label class="form-label">🔍 प्रणालीमा भएको जीवनसाथी खोज्नुहोस्</label>
        <div class="relative">
          <input type="text" id="spouseSearchInput" class="form-input pr-8" autocomplete="off"
            placeholder="नाम (नेपाली / English), ID वा सदस्य नं…">
          <span id="spouseSearchSpinner" class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-xs">⏳</span>
        </div>
        <input type="hidden" name="union_spouse_id" id="union_spouse_id" value="{{ old('union_spouse_id') }}">
        <div id="spouseSelected" class="mt-1.5 items-center gap-2 px-3 py-2 bg-green-50 border border-green-300 rounded-lg text-sm text-green-800" style="display:none">
          <span>✅</span>
          <span id="spouseSelectedName"></span>
          <button type="button" id="clearSpouseBtn" class="ml-auto text-xs text-red-500 hover:underline">Remove</button>
        </div>
        <div id="spouseDropdown" class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-52 overflow-y-auto"></div>
        @error('union_spouse_id')<p class="form-error">{{ $message }}</p>@enderror
      </div>

      <div class="flex items-center gap-3 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
        <span class="h-px flex-1 bg-slate-200"></span> वा नयाँ जीवनसाथी <span class="h-px flex-1 bg-slate-200"></span>
      </div>

      {{-- 2. new spouse: photo + details (disabled while an existing spouse is selected) --}}
      <div id="newSpouseCard" class="rounded-xl border border-slate-200 bg-slate-50/50 p-3 sm:p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex shrink-0 flex-col items-center gap-2 sm:w-36">
            <div id="unionSpousePhotoPreview" class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-slate-300 bg-white text-3xl text-slate-300">👤</div>
            <div class="flex w-full flex-wrap items-start justify-center gap-x-1.5">
              <label for="unionSpousePhoto" class="mt-1.5 inline-flex cursor-pointer items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">🖼 फाइल</label>
              <input type="file" name="spouse_photo" id="unionSpousePhoto" data-new-spouse data-camera data-camera-label="📷 क्यामेरा"
                accept="image/jpeg,image/jpg,image/png,image/webp" class="sr-only">
              <p class="mt-1 w-full text-center text-[10px] text-slate-400">Max 500 KB</p>
            </div>
            @error('spouse_photo')<p class="form-error text-center">{{ $message }}</p>@enderror
          </div>

          <div class="min-w-0 flex-1 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="form-label">नेपाली नाम <span class="text-red-500">*</span></label>
                <input type="text" name="union_spouse_name" data-new-spouse class="form-input" value="{{ old('union_spouse_name') }}" placeholder="जीवनसाथीको नाम">
                @error('union_spouse_name')<p class="form-error">{{ $message }}</p>@enderror
              </div>
              <div>
                <label class="form-label">Full Name (English)</label>
                <input type="text" name="union_spouse_name_np" data-new-spouse class="form-input" value="{{ old('union_spouse_name_np') }}" placeholder="English name">
              </div>
              <div>
                <label class="form-label">Limbu name</label>
                <input type="text" name="union_spouse_name_limbu" data-new-spouse class="form-input" value="{{ old('union_spouse_name_limbu') }}" placeholder="Limbu script">
              </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="form-label">लिङ्ग</label>
                @php $defaultSpouseGender = old('union_spouse_gender', ($p->gender ?? old('gender', 'male')) === 'female' ? 'male' : 'female'); @endphp
                <select name="union_spouse_gender" data-new-spouse class="form-input">
                  @foreach(['female' => 'पत्नी (Female)', 'male' => 'पति (Male)', 'other' => 'Other', 'unknown' => 'Unknown'] as $k => $v)
                    <option value="{{ $k }}" @selected($defaultSpouseGender === $k)>{{ $v }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label class="form-label">मोबाइल</label>
                <input type="text" name="spouse_mobile" data-new-spouse inputmode="tel" class="form-input" value="{{ old('spouse_mobile') }}">
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-3">
          <div>
            <label class="form-label">जन्म मिति (A.D.)</label>
            <input type="date" name="spouse_birth_date" data-new-spouse class="form-input" value="{{ old('spouse_birth_date') }}">
            @error('spouse_birth_date')<p class="form-error">{{ $message }}</p>@enderror
          </div>
          <div>
            <label class="form-label">जन्म मिति (B.S.)</label>
            <input type="text" name="spouse_birth_date_bs" data-new-spouse class="form-input" value="{{ old('spouse_birth_date_bs') }}" placeholder="2055-04-12">
          </div>
          <div class="col-span-2 md:col-span-1">
            <label class="form-label">माइती / जन्मस्थान</label>
            <input type="text" name="spouse_birth_place" data-new-spouse class="form-input" value="{{ old('spouse_birth_place') }}">
          </div>
          <div>
            <label class="form-label">बुबाको नाम</label>
            <input type="text" name="spouse_father_name" data-new-spouse class="form-input" value="{{ old('spouse_father_name') }}">
          </div>
          <div>
            <label class="form-label">आमाको नाम</label>
            <input type="text" name="spouse_mother_name" data-new-spouse class="form-input" value="{{ old('spouse_mother_name') }}">
          </div>
          <div class="col-span-2 md:col-span-1">
            <label class="form-label">हालको ठेगाना</label>
            <input type="text" name="spouse_address" data-new-spouse class="form-input" value="{{ old('spouse_address') }}">
          </div>
          <div>
            <label class="form-label">शिक्षा</label>
            <input type="text" name="spouse_education" data-new-spouse class="form-input" value="{{ old('spouse_education') }}">
          </div>
          <div>
            <label class="form-label">पेशा</label>
            <input type="text" name="spouse_occupation" data-new-spouse class="form-input" value="{{ old('spouse_occupation') }}">
          </div>
        </div>
      </div>

      {{-- 3. marriage details --}}
      <div class="rounded-xl border border-pink-100 bg-pink-50/40 p-3 sm:p-4">
        <div class="mb-2 text-[11px] font-bold uppercase tracking-wide text-pink-500">विवाह विवरण</div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
          <div>
            <label class="form-label">विवाह प्रकार</label>
            <select name="union_type" class="form-input">
              @foreach(['married' => '💍 Married','divorced' => '💔 Divorced','widowed' => '🕊️ Widowed','separated' => '🔗 Separated'] as $k => $v)
                <option value="{{ $k }}" @selected(old('union_type','married') === $k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label class="form-label">विवाह मिति (A.D.)</label>
            <input type="date" name="union_start_date" class="form-input" value="{{ old('union_start_date') }}">
          </div>
          <div class="col-span-2">
            <label class="form-label">Notes</label>
            <input type="text" name="union_notes" class="form-input" value="{{ old('union_notes') }}" placeholder="थप नोट…">
          </div>
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
  const newSpouseInputs = Array.from(document.querySelectorAll('[data-new-spouse]'));

  /* ── New spouse photo preview (file or camera) ── */
  (() => {
    const input = document.getElementById('unionSpousePhoto');
    const preview = document.getElementById('unionSpousePhotoPreview');
    if (!input || !preview) return;
    const empty = preview.className;
    input.addEventListener('change', () => {
      const f = input.files?.[0];
      if (!f || f.size > 500 * 1024) {
        if (f) { alert('फोटो 500 KB भन्दा ठूलो छ।'); input.value = ''; }
        preview.className = empty; preview.textContent = '👤'; return;
      }
      preview.className = 'h-24 w-24 overflow-hidden rounded-full border-2 border-pink-200 bg-white';
      preview.innerHTML = `<img src="${URL.createObjectURL(f)}" alt="" class="h-full w-full object-cover">`;
    });
  })();

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
    newSpouseInputs.forEach(input => { if (input.tagName !== 'SELECT') input.value = ''; });
    document.getElementById('unionSpousePhoto')?.dispatchEvent(new Event('change'));
    document.getElementById('newSpouseCard')?.classList.add('opacity-50');
  }

  function clearSpouse() {
    spouseHidden.value = '';
    spouseSelected.style.display = 'none';
    spouseInput.value = '';
    document.getElementById('newSpouseCard')?.classList.remove('opacity-50');
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
    const max = 500 * 1024;
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

@if(!empty($orderParent))
<script>
(() => {
  const gender = document.getElementById('personGenderSelect');
  const select = document.getElementById('personBirthOrder');
  const taken = @json($takenOrders ?? []);
  const RELATION = { male: 'छोरा', female: 'छोरी' };
  const np = n => String(n).replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
  const MAX = {{ \App\Support\SiblingOrder::MAX }};
  let wanted = parseInt(select.dataset.current, 10) || null;

  function fill() {
    const g = gender.value;
    const used = taken[g] || {};
    const nums = Object.keys(used).map(Number).sort((a, b) => a - b);
    let options = '<option value="">— परिवर्तन नगर्ने —</option>';
    for (let n = 1; n <= MAX; n++) options += `<option value="${n}">${np(n)}</option>`;
    select.innerHTML = options;
    select.value = wanted ? String(wanted) : '';

    document.getElementById('personOrderRelation').textContent = RELATION[g] || 'सन्तान';
    document.getElementById('personOrderTaken').textContent = nums.length
      ? 'अरू सन्तान: ' + nums.map(n => `${np(n)} ${used[n]}`).join(', ')
      : 'अरू कोही सन्तान छैनन्।';
  }

  gender.addEventListener('change', fill);
  select.addEventListener('change', () => { wanted = parseInt(select.value, 10) || null; });
  fill();
})();
</script>
@endif

<script>
// सदस्यको प्रकार follows gender while it still holds the automatic value (बुहारी / manual picks are kept)
(() => {
  const gender = document.querySelector('#personFormGrid [name="gender"]');
  const type = document.getElementById('personMemberType');
  if (!gender || !type) return;
  const AUTO = { male: @js(\App\Support\MemberType::DAJU_BHAI), female: @js(\App\Support\MemberType::DIDI_BAHINI) };
  let prev = gender.value;
  gender.addEventListener('change', () => {
    if (!type.value || type.value === (AUTO[prev] || '')) type.value = AUTO[gender.value] || '';
    prev = gender.value;
  });
})();
</script>

@include('partials.photo-camera')
