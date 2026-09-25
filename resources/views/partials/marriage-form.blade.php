{{--
  विवाह (marriage) form with new-spouse photo and details.
  @include('partials.marriage-form', ['marriagePerson' => $person])  → fixed person (member page)
  @include('partials.marriage-form', ['marriagePerson' => null, 'returnBack' => true])
      → person chosen in JS: window.openMarriageForm({ id, display_name, gender, pusta })
  Open with document.getElementById('marriageModal').style.display = 'flex' or openMarriageForm().
--}}
@php
    $mp = $marriagePerson ?? null;
    $returnBack = $returnBack ?? false;
    $isAdminUser = auth()->check() && auth()->user()->isAdmin();
    $isMarriageOld = old('form_type') === 'marriage';
    $mId = $isMarriageOld ? old('marriage_person_id', $mp?->id) : $mp?->id;
    $mName = $isMarriageOld ? old('marriage_person_name', $mp?->display_name) : $mp?->display_name;
    $mGender = $isMarriageOld ? old('marriage_person_gender', $mp?->gender) : $mp?->gender;
    $defaultSpouseGender = $mGender === 'male' ? 'female' : ($mGender === 'female' ? 'male' : 'unknown');
@endphp
@once
<style>
    #marriageModal .mLabel { display: block; margin-bottom: 3px; font-size: 11.5px; font-weight: 600; color: #475569; }
    #marriageModal .mInput { width: 100%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px; font-size: 13px; color: #0f172a; background: #fff; transition: border-color .15s, box-shadow .15s; }
    #marriageModal .mInput:focus { outline: none; border-color: #60a5fa; box-shadow: 0 0 0 3px #eff6ff; }
    #marriageModal .mSection { margin-bottom: 8px; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
    #marriageModal .mErr { margin-top: 3px; font-size: 11.5px; font-weight: 600; color: #dc2626; }
</style>

<div id="marriageModal" style="display:none"
     class="fixed inset-0 z-[80] items-end sm:items-center justify-center bg-slate-900/45 backdrop-blur-sm p-0 sm:p-6"
     x-data="{ mode: @js($isMarriageOld && old('spouse_person_id') ? 'existing' : 'new') }">
    <div class="flex w-full sm:max-w-3xl max-h-[94vh] flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl border border-slate-200 bg-white shadow-2xl">

        {{-- Header --}}
        <div class="flex items-center gap-3 border-b border-slate-100 bg-gradient-to-r from-rose-50 to-amber-50 px-4 sm:px-5 py-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-xl ring-1 ring-rose-100">💍</div>
            <div class="min-w-0">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-rose-400">{{ $isAdminUser ? \App\Support\FrontendLocale::text('विवाह थप्नुहोस्') : \App\Support\FrontendLocale::text('विवाह जानकारी अनुरोध') }}</div>
                <div class="truncate font-bold text-slate-900" id="marriagePersonName">{{ $mName ?? '—' }}</div>
            </div>
            <button type="button" data-marriage-close
                    class="ml-auto h-8 w-8 shrink-0 rounded-full text-lg leading-none text-slate-500 hover:bg-white/70" aria-label="{{ \App\Support\FrontendLocale::text('बन्द गर्नुहोस्') }}">×</button>
        </div>

        <form method="POST" id="marriageForm" enctype="multipart/form-data"
              action="{{ $mId ? \App\Support\FrontendLocale::route('request.marriage', $mId) : '#' }}" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <input type="hidden" name="form_type" value="marriage">
            <input type="hidden" name="marriage_person_id" id="marriagePersonId" value="{{ $mId }}">
            <input type="hidden" name="marriage_person_name" id="marriagePersonNameInput" value="{{ $mName }}">
            <input type="hidden" name="marriage_person_gender" id="marriagePersonGender" value="{{ $mGender }}">
            @if($returnBack) <input type="hidden" name="return_to" value="back"> @endif

            <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 sm:px-5 py-4 text-sm">

                {{-- Who is the spouse? --}}
                <div class="grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1 text-sm font-semibold">
                    <button type="button" @click="mode = 'new'" class="rounded-lg px-3 py-2 transition"
                            :class="mode === 'new' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ \App\Support\FrontendLocale::text('＋ नयाँ जीवनसाथी') }}
                    </button>
                    <button type="button" @click="mode = 'existing'" class="rounded-lg px-3 py-2 transition"
                            :class="mode === 'existing' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ \App\Support\FrontendLocale::text('🔍 प्रणालीमा भएको व्यक्ति') }}
                    </button>
                </div>

                {{-- Existing person --}}
                <div x-show="mode === 'existing'" x-cloak class="relative">
                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('ID, नाम वा सदस्य नं. ले खोज्नुहोस्') }}</label>
                    <input type="text" id="spouseExistingSearch" placeholder="{{ \App\Support\FrontendLocale::text('e.g. 42, Sita, सीता…') }}" class="mInput" autocomplete="off">
                    <input type="hidden" name="spouse_person_id" id="spousePersonId" value="{{ $isMarriageOld ? old('spouse_person_id') : '' }}" :disabled="mode !== 'existing'">
                    @if($isMarriageOld) @error('spouse_person_id') <div class="mErr">{{ $message }}</div> @enderror @endif
                    <div id="spouseSearchResults" class="absolute left-0 right-0 z-10 mt-1 hidden max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                    <div id="selectedSpouseBox" class="mt-2 hidden items-center gap-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
                        <span class="font-semibold">{{ \App\Support\FrontendLocale::text('छानिएको:') }}</span>
                        <span id="selectedSpouseName" class="min-w-0 truncate"></span>
                        <button type="button" id="clearSelectedSpouse" class="ml-auto shrink-0 text-xs font-semibold text-rose-600 hover:underline">{{ \App\Support\FrontendLocale::text('हटाउनुहोस्') }}</button>
                    </div>
                    <p class="mt-2 text-[11px] text-slate-400">{{ \App\Support\FrontendLocale::text('प्रणालीमा पहिले नै भएको व्यक्तिको विवरण उसको आफ्नै प्रोफाइलबाट सम्पादन गर्नुहोस्।') }}</p>
                </div>

                {{-- New spouse: photo + details --}}
                <div x-show="mode === 'new'" class="space-y-5">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex shrink-0 flex-col items-center gap-2 sm:w-40">
                            <div id="spousePhotoPreview"
                                 class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-slate-300 bg-slate-50 text-3xl text-slate-300">👤</div>
                            <div class="flex w-full flex-wrap items-start justify-center gap-x-1.5">
                                <label for="spousePhotoInput" class="inline-flex cursor-pointer items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">{{ \App\Support\FrontendLocale::text('🖼 फाइल') }}</label>
                                <input type="file" name="spouse_photo" id="spousePhotoInput" data-camera data-camera-label="📷 क्यामेरा" class="sr-only"
                                       accept="image/jpeg,image/jpg,image/png,image/webp" :disabled="mode !== 'new'">
                                <p class="mt-1 w-full text-center text-[10px] text-slate-400">{{ \App\Support\FrontendLocale::text('Max 500 KB') }}</p>
                                @if($isMarriageOld) @error('spouse_photo') <div class="mErr">{{ $message }}</div> @enderror @endif
                            </div>
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('नेपाली नाम') }} <span class="text-rose-500">*</span></label>
                                <input type="text" name="spouse_name" value="{{ $isMarriageOld ? old('spouse_name') : '' }}" placeholder="{{ \App\Support\FrontendLocale::text('जीवनसाथीको पूरा नाम') }}" class="mInput" :disabled="mode !== 'new'">
                                @if($isMarriageOld) @error('spouse_name') <div class="mErr">{{ $message }}</div> @enderror @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('Full Name (English)') }}</label>
                                    <input type="text" name="spouse_name_np" value="{{ $isMarriageOld ? old('spouse_name_np') : '' }}" class="mInput" :disabled="mode !== 'new'">
                                </div>
                                <div>
                                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('Limbu / ᤕᤰᤌᤢᤱ नाम') }}</label>
                                    <input type="text" name="spouse_name_limbu" value="{{ $isMarriageOld ? old('spouse_name_limbu') : '' }}" class="mInput" :disabled="mode !== 'new'">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('लिङ्ग') }}</label>
                                    @php $sg = $isMarriageOld ? old('spouse_gender', $defaultSpouseGender) : $defaultSpouseGender; @endphp
                                    <select name="spouse_gender" id="spouseGenderSelect" class="mInput" :disabled="mode !== 'new'">
                                        <option value="female" @selected($sg === 'female')>{{ \App\Support\FrontendLocale::text('पत्नी (Female)') }}</option>
                                        <option value="male" @selected($sg === 'male')>{{ \App\Support\FrontendLocale::text('पति (Male)') }}</option>
                                        <option value="other" @selected($sg === 'other')>{{ \App\Support\FrontendLocale::text('अन्य') }}</option>
                                        <option value="unknown" @selected($sg === 'unknown')>{{ \App\Support\FrontendLocale::text('थाहा छैन') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('मोबाइल') }}</label>
                                    <input type="text" name="spouse_mobile" inputmode="tel" value="{{ $isMarriageOld ? old('spouse_mobile') : '' }}" class="mInput" :disabled="mode !== 'new'">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mSection">{{ \App\Support\FrontendLocale::text('जन्म र माइती') }}</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('जन्म मिति (A.D.)') }}</label>
                                <input type="date" name="spouse_birth_date" value="{{ $isMarriageOld ? old('spouse_birth_date') : '' }}" class="mInput" :disabled="mode !== 'new'">
                                @if($isMarriageOld) @error('spouse_birth_date') <div class="mErr">{{ $message }}</div> @enderror @endif
                            </div>
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('जन्म मिति (B.S.)') }}</label>
                                <input type="text" name="spouse_birth_date_bs" placeholder="2055-04-12" value="{{ $isMarriageOld ? old('spouse_birth_date_bs') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('माइती / जन्मस्थान') }}</label>
                                <input type="text" name="spouse_birth_place" value="{{ $isMarriageOld ? old('spouse_birth_place') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('बुबाको नाम') }}</label>
                                <input type="text" name="spouse_father_name" value="{{ $isMarriageOld ? old('spouse_father_name') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('आमाको नाम') }}</label>
                                <input type="text" name="spouse_mother_name" value="{{ $isMarriageOld ? old('spouse_mother_name') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('हालको ठेगाना') }}</label>
                                <input type="text" name="spouse_address" value="{{ $isMarriageOld ? old('spouse_address') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mSection">{{ \App\Support\FrontendLocale::text('शिक्षा र पेशा') }}</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('शिक्षा') }}</label>
                                <input type="text" name="spouse_education" value="{{ $isMarriageOld ? old('spouse_education') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                            <div>
                                <label class="mLabel">{{ \App\Support\FrontendLocale::text('पेशा') }}</label>
                                <input type="text" name="spouse_occupation" value="{{ $isMarriageOld ? old('spouse_occupation') : '' }}" class="mInput" :disabled="mode !== 'new'">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Marriage --}}
                <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-3 sm:p-4">
                    <div class="mSection text-rose-400">{{ \App\Support\FrontendLocale::text('विवाह विवरण') }}</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mLabel">{{ \App\Support\FrontendLocale::text('सम्बन्धको प्रकार') }}</label>
                            @php $t = $isMarriageOld ? old('type', 'married') : 'married'; @endphp
                            <select name="type" class="mInput">
                                <option value="married" @selected($t === 'married')>{{ \App\Support\FrontendLocale::text('विवाहित (Married)') }}</option>
                                <option value="partner" @selected($t === 'partner')>{{ \App\Support\FrontendLocale::text('Partner') }}</option>
                                <option value="divorced" @selected($t === 'divorced')>{{ \App\Support\FrontendLocale::text('सम्बन्ध विच्छेद (Divorced)') }}</option>
                                <option value="widowed" @selected($t === 'widowed')>{{ \App\Support\FrontendLocale::text('विधवा/विधुर (Widowed)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mLabel">{{ \App\Support\FrontendLocale::text('विवाह मिति (A.D.)') }}</label>
                            <input type="date" name="start_date" value="{{ $isMarriageOld ? old('start_date') : '' }}" class="mInput">
                            @if($isMarriageOld) @error('start_date') <div class="mErr">{{ $message }}</div> @enderror @endif
                        </div>
                        <div class="col-span-2">
                            <label class="mLabel">{{ \App\Support\FrontendLocale::text('थप नोट') }}</label>
                            <input type="text" name="notes" value="{{ $isMarriageOld ? old('notes') : '' }}" class="mInput">
                        </div>
                    </div>
                </div>

                @guest
                <div>
                    <label class="mLabel">{{ \App\Support\FrontendLocale::text('तपाईंको नाम') }} <span class="text-rose-500">*</span></label>
                    <input type="text" name="submitted_name" value="{{ old('submitted_name') }}" class="mInput max-w-sm">
                </div>
                @endguest
            </div>

            {{-- Footer --}}
            <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/80 px-4 sm:px-5 py-3">
                <span class="hidden sm:block text-[11px] text-slate-400">{{ $isAdminUser ? \App\Support\FrontendLocale::text('सिधै थपिन्छ।') : \App\Support\FrontendLocale::text('Admin को स्वीकृतिपछि थपिन्छ।') }}</span>
                <button type="button" data-marriage-close class="ml-auto rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ \App\Support\FrontendLocale::text('रद्द') }}</button>
                <button type="submit" class="rounded-lg bg-rose-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                    {{ $isAdminUser ? \App\Support\FrontendLocale::text('💍 विवाह थप्नुहोस्') : \App\Support\FrontendLocale::text('अनुरोध पठाउनुहोस्') }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const modal = document.getElementById('marriageModal');
    const form = document.getElementById('marriageForm');
    const actionBase = @json(url('/person'));
    const show = () => { modal.style.display = 'flex'; };
    const hide = () => { modal.style.display = 'none'; };

    // Open for a person chosen on the page (relationships panel)
    window.openMarriageForm = (p) => {
        form.reset();
        form.action = `${actionBase}/${p.id}/request-marriage`;
        document.getElementById('marriagePersonId').value = p.id;
        document.getElementById('marriagePersonNameInput').value = p.display_name || '';
        document.getElementById('marriagePersonGender').value = p.gender || '';
        document.getElementById('marriagePersonName').textContent = p.display_name || '—';
        document.getElementById('spouseGenderSelect').value = p.gender === 'male' ? 'female' : (p.gender === 'female' ? 'male' : 'unknown');
        resetPhoto(); clearSelected();
        show();
    };

    modal.querySelectorAll('[data-marriage-close]').forEach(b => b.addEventListener('click', hide));
    modal.addEventListener('click', e => { if (e.target === modal) hide(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && modal.style.display === 'flex') hide(); });
    @if($isMarriageOld && $errors->any()) show(); @endif

    // Photo preview (file or camera), 500 KB limit
    const photoInput = document.getElementById('spousePhotoInput');
    const preview = document.getElementById('spousePhotoPreview');
    function resetPhoto() {
        preview.innerHTML = '👤';
        preview.className = 'flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-slate-300 bg-slate-50 text-3xl text-slate-300';
    }
    photoInput.addEventListener('change', () => {
        const f = photoInput.files?.[0];
        if (!f) return resetPhoto();
        if (f.size > 500 * 1024) { alert(@js(\App\Support\FrontendLocale::text('फोटो 500 KB भन्दा ठूलो छ। सानो फोटो छान्नुहोस्।'))); photoInput.value = ''; return resetPhoto(); }
        const url = URL.createObjectURL(f);
        preview.className = 'h-24 w-24 overflow-hidden rounded-full border-2 border-rose-200 bg-white';
        preview.innerHTML = `<img src="${url}" alt="" class="h-full w-full object-cover">`;
    });

    // Existing person search
    const searchInput = document.getElementById('spouseExistingSearch');
    const resultsBox = document.getElementById('spouseSearchResults');
    const hiddenId = document.getElementById('spousePersonId');
    const selectedBox = document.getElementById('selectedSpouseBox');
    const selectedName = document.getElementById('selectedSpouseName');
    const searchUrl = @json(\App\Support\FrontendLocale::route('people.search'));
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    let timer = null;

    function showSelected(id, label) {
        hiddenId.value = id;
        selectedName.textContent = `${label} (#${id})`;
        selectedBox.classList.remove('hidden'); selectedBox.classList.add('flex');
        resultsBox.classList.add('hidden');
        searchInput.value = '';
    }
    function clearSelected() {
        hiddenId.value = '';
        selectedBox.classList.add('hidden'); selectedBox.classList.remove('flex');
    }
    document.getElementById('clearSelectedSpouse').addEventListener('click', clearSelected);

    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        const term = searchInput.value.trim();
        if (!term) { resultsBox.classList.add('hidden'); return; }
        timer = setTimeout(async () => {
            const rows = await (await fetch(`${searchUrl}${searchUrl.includes('?') ? '&' : '?'}term=${encodeURIComponent(term)}`)).json();
            const self = String(document.getElementById('marriagePersonId').value);
            const list = rows.filter(p => String(p.id) !== self);
            resultsBox.innerHTML = list.length ? list.map(p => {
                const label = [p.display_name, p.display_name_np, p.display_name_limbu].filter(Boolean).join(' / ');
                const meta = [p.member_number || `#${p.id}`, p.gender, p.pusta ? `पु.${p.pusta}` : null, p.father_name ? `बुबा: ${p.father_name}` : null].filter(Boolean).join(' · ');
                return `<button type="button" data-id="${p.id}" data-label="${esc(label)}" class="w-full border-b border-slate-100 px-3 py-2 text-left last:border-0 hover:bg-rose-50">
                    <div class="text-sm font-semibold text-slate-800">${esc(label)}</div><div class="text-[11px] text-slate-400">${esc(meta)}</div></button>`;
            }).join('') : '<div class="px-3 py-3 text-xs text-slate-400">भेटिएन — "नयाँ जीवनसाथी" मा नाम लेख्नुहोस्।</div>';
            resultsBox.classList.remove('hidden');
            resultsBox.querySelectorAll('button[data-id]').forEach(b => b.addEventListener('click', () => showSelected(b.dataset.id, b.dataset.label)));
        }, 250);
    });
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) resultsBox.classList.add('hidden');
    });
    if (hiddenId.value) showSelected(hiddenId.value, 'Person');
})();
</script>
@endonce
