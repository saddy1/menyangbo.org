@extends('layouts.app')
@section('title', $person->display_name . \App\Support\FrontendLocale::text(' • प्रोफाइल'))

@section('content')

<style>
    .inputCompact {
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        border-radius: 8px;
        width: 100%;
        font-size: 13px;
        color: #0f172a;
        background-color: #ffffff;
        transition: all 0.2s;
    }
    .inputCompact::placeholder { color: #94a3b8; }
    .inputCompact:focus {
        outline: none;
        box-shadow: 0 0 0 3px #eff6ff;
        border-color: #3b82f6;
    }

    .textareaCompact {
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        border-radius: 8px;
        width: 100%;
        min-height: 80px;
        font-size: 13px;
        color: #0f172a;
        background-color: #ffffff;
        transition: all 0.2s;
    }
    .textareaCompact::placeholder { color: #94a3b8; }
    .textareaCompact:focus {
        outline: none;
        box-shadow: 0 0 0 3px #eff6ff;
        border-color: #3b82f6;
    }

    .editLabel { display: block; margin-bottom: 3px; font-size: 11.5px; font-weight: 600; color: #475569; }
    .editSection { margin-bottom: 8px; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
    #editModal .inputCompact { padding: 6px 10px; }
    #editModal .textareaCompact { min-height: 0; padding: 6px 10px; }

    .err {
        margin-top: 4px;
        font-size: 12px;
        color: #dc2626;
        font-weight: 500;
    }
    
    /* Smooth modal transition */
    .modal-backdrop { transition: opacity 0.3s ease-out; }
    .modal-panel { transition: transform 0.3s ease-out, opacity 0.3s ease-out; }
</style>

@php $canInlineEdit = auth()->check() && auth()->user()->isAdmin(); @endphp
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Success --}}
    @if (session('success_message'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-900 font-semibold">
            {{ session('success_message') }}
        </div>
    @endif

    {{-- Admin mode banner --}}
    @auth
    @if(Auth::user()->isAdmin())
    <div class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-indigo-900 text-sm flex items-center gap-3 flex-wrap">
        <span class="text-base">🛡️</span>
        <span><strong>{{ \App\Support\FrontendLocale::text('Admin Direct Edit Mode') }}</strong> {{ \App\Support\FrontendLocale::text('— तपाईं Admin हुनुहुन्छ। सबै परिवर्तनहरू सिधै लागू हुनेछन्, अनुरोध जाँदैन।') }}
            <span class="block text-xs text-indigo-700 mt-0.5">{{ \App\Support\FrontendLocale::text('✎ कुनै पनि विवरणमा क्लिक गरेर यहीँबाट सिधै सच्याउनुहोस् — Enter थिच्दा तुरुन्तै सेभ हुन्छ।') }}</span></span>
        <a href="{{ \App\Support\FrontendLocale::route('admin.persons.edit', $person) }}"
           class="ml-auto shrink-0 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors">
            {{ \App\Support\FrontendLocale::text('Admin Edit →') }}
        </a>
    </div>
    @endif
    @endauth

    {{-- Auth notice --}}
    @guest
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 text-sm flex items-center justify-between gap-3 flex-wrap">
        <span>{{ \App\Support\FrontendLocale::text('अनुरोध पठाउन (Add Child, Edit, Deceased, Add Wife)') }} <strong>{{ \App\Support\FrontendLocale::text('लगइन') }}</strong> {{ \App\Support\FrontendLocale::text('आवश्यक छ।') }}</span>
        <div class="flex gap-2">
            <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}" class="bg-blue-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-700">{{ \App\Support\FrontendLocale::text('लगइन') }}</a>
            <a href="{{ \App\Support\FrontendLocale::route('register') }}" class="border border-blue-600 text-blue-600 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-50">{{ \App\Support\FrontendLocale::text('दर्ता') }}</a>
        </div>
    </div>
    @endguest

    {{-- Errors summary --}}
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <div class="font-bold">{{ \App\Support\FrontendLocale::text('कृपया तलका त्रुटिहरू सुधार्नुहोस्:') }}</div>
            <ul class="list-disc pl-5 text-sm mt-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between gap-3 flex-wrap">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-slate-900 truncate" data-sync="display_name">{{ $person->display_name }}</h1>

            <div class="text-sm text-slate-600 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span>{{ \App\Support\FrontendLocale::text('सदस्य नं.') }}: <span class="font-semibold text-slate-900">{{ $person->member_no ?: '—' }}</span></span>
                <span class="text-slate-300">•</span>
                <span>{{ \App\Support\FrontendLocale::text('Pusta:') }} <span class="font-semibold text-slate-900" @if($canInlineEdit) data-edit="pusta" data-type="text" data-value="{{ $person->pusta }}" @endif>{{ $person->pusta ?? '—' }}</span></span>
                <span class="text-slate-300">•</span>
                <span>{{ \App\Support\FrontendLocale::text('Gender:') }} <span class="font-semibold text-slate-900" @if($canInlineEdit) data-edit="gender" data-type="gender" data-value="{{ $person->gender }}" @endif>{{ ucfirst($person->gender ?? 'unknown') }}</span></span>

                @if ($person->is_deceased)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold border border-red-100">
                        {{ \App\Support\FrontendLocale::text('Deceased') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="flex gap-2 shrink-0">
            <a href="{{ \App\Support\FrontendLocale::route('tree.index', ['root_id' => $person->id]) }}"
               class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition-colors">
                {{ \App\Support\FrontendLocale::text('Open in Tree') }}
            </a>
            <a href="{{ url()->previous() }}"
               class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition-colors">
                {{ \App\Support\FrontendLocale::text('Back') }}
            </a>
        </div>
    </div>

    {{-- Top Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">

        {{-- Profile --}}
        <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-sm">
            <div class="relative text-center mb-6">
                @if ($person->is_deceased)
                    <span class="absolute right-0 top-0 text-xs font-semibold text-red-700 bg-red-50 border border-red-100 px-3 py-1 rounded-full">
                        {{ \App\Support\FrontendLocale::text('मृत्यु विवरण सहित') }}
                    </span>
                @endif
                <div class="flex flex-col items-center gap-3">
                    <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center text-5xl shrink-0 border-4 border-white ring-1 ring-slate-200 shadow-sm">
                        @if(!empty($person->photo_path) && file_exists(public_path($person->photo_path)))
                            <img src="{{ asset($person->photo_path) }}" class="w-full h-full object-cover" alt="{{ $person->display_name }}">
                        @else
                            {{ ($person->gender ?? '') === 'female' ? '👩' : (($person->gender ?? '') === 'male' ? '👨' : '🧑') }}
                        @endif
                    </div>

                    {{-- Admin photo upload, right under the photo: camera or file, uploads immediately --}}
                    @if($canInlineEdit)
                    <form method="POST" action="{{ \App\Support\FrontendLocale::route('person.upload-photo', $person) }}" enctype="multipart/form-data"
                          id="memberPhotoForm" class="flex flex-col items-center gap-1">
                        @csrf
                        <div class="flex flex-wrap items-start justify-center gap-2">
                            <label for="memberPhotoInput"
                                   class="mt-1.5 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                {{ \App\Support\FrontendLocale::text('🖼 फाइलबाट छान्नुहोस्') }}
                            </label>
                            <input type="file" name="photo" id="memberPhotoInput" data-camera class="sr-only"
                                   accept="image/jpeg,image/jpg,image/png,image/webp">
                        </div>
                        <p id="memberPhotoErr" class="text-[11px] font-semibold text-red-500" style="display:none">{{ \App\Support\FrontendLocale::text('⚠ 500 KB भन्दा बढी छ — सानो फोटो छान्नुहोस्।') }}</p>
                        <p id="memberPhotoBusy" class="text-[11px] font-semibold text-indigo-600" style="display:none">{{ \App\Support\FrontendLocale::text('अपलोड हुँदैछ…') }}</p>
                        <p class="text-[10px] text-slate-400">{{ \App\Support\FrontendLocale::text('Max 500 KB · JPEG / PNG / WebP') }}</p>
                    </form>
                    <script>
                    document.getElementById('memberPhotoInput')?.addEventListener('change', function () {
                        const err = document.getElementById('memberPhotoErr');
                        const file = this.files && this.files[0];
                        if (!file) return;
                        if (file.size > 500 * 1024) {
                            err.style.display = 'block';
                            this.value = '';
                            return;
                        }
                        err.style.display = 'none';
                        document.getElementById('memberPhotoBusy').style.display = 'block';
                        this.form.requestSubmit();
                    });
                    </script>
                    @endif
                    <div>
                        <div class="text-2xl font-bold text-slate-900" data-sync="display_name">{{ $person->display_name }}</div>
                        <div class="mt-1 text-sm text-slate-500">
                            <span data-sync="display_name_np">{{ $person->display_name_np ?: \App\Support\FrontendLocale::text('नेपाली नाम छैन') }}</span>
                            @if($person->display_name_limbu)
                                <span class="mx-2 text-slate-300">•</span>{{ $person->display_name_limbu }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-slate-900">
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('सदस्य न.') }}</span> <span class="font-medium">{{ $person->member_no ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('सदस्यको प्रकार') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="member_type" data-type="member_type" data-value="{{ $person->member_type }}" @endif>{{ $person->member_type ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('नेपाली नाम') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="display_name" data-type="text" data-value="{{ $person->display_name }}" @endif>{{ $person->display_name ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('Full Name (English)') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="display_name_np" data-type="text" data-value="{{ $person->display_name_np }}" @endif>{{ $person->display_name_np ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('Limbu Name / ᤕᤰᤌᤢᤱ नाम') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="display_name_limbu" data-type="text" data-value="{{ $person->display_name_limbu }}" @endif>{{ $person->display_name_limbu ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('सदस्यता') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="membership" data-type="text" data-value="{{ $person->membership }}" @endif>{{ $person->membership ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('जन्म मिति (A.D.)') }}</span>
                    <span class="font-medium" @if($canInlineEdit) data-edit="birth_date" data-type="date" data-value="{{ $person->birth_date?->format('Y-m-d') }}" @endif>{{ $person->birth_date ? $person->birth_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('जन्म मिति (B.S.)') }}</span>
                    <span class="font-medium" @if($canInlineEdit) data-edit="birth_date_bs" data-type="text" data-value="{{ $person->birth_date_bs }}" @endif>{{ $person->birth_date_bs ?? '-' }}</span>
                </div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('जन्मस्थान') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="birth_place" data-type="text" data-value="{{ $person->birth_place }}" @endif>{{ $person->birth_place ?? '-' }}</span></div>

                {{-- Father / Mother --}}
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('बुबाको नाम') }}</span>
                    @if (!empty($father))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ \App\Support\FrontendLocale::route('member.page', $father->id) }}">
                            {{ $father->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>

                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('आमाको नाम') }}</span>
                    @if (!empty($mother))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ \App\Support\FrontendLocale::route('member.page', $mother->id) }}">
                            {{ $mother->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>

                {{-- Grandparents (only if data exists) --}}
                @if(!empty($grandfather) || !empty($grandmother))
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('हजुरबुबाको नाम') }}</span>
                    @if(!empty($grandfather))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ \App\Support\FrontendLocale::route('member.page', $grandfather->id) }}">
                            {{ $grandfather->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('हजुरआमाको नाम') }}</span>
                    @if(!empty($grandmother))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ \App\Support\FrontendLocale::route('member.page', $grandmother->id) }}">
                            {{ $grandmother->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>
                @endif

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('घरको ठेगाना') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="address" data-type="text" data-value="{{ $person->address }}" @endif>{{ $person->address ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('मोबाइल नम्बर') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="mobile" data-type="text" data-value="{{ $person->mobile }}" @endif>{{ $person->mobile ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('Email ID') }}</span> <span class="font-medium break-all" @if($canInlineEdit) data-edit="email" data-type="email" data-value="{{ $person->email }}" @endif>{{ $person->email ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('शिक्षा') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="education" data-type="text" data-value="{{ $person->education }}" @endif>{{ $person->education ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('पेशा') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="occupation" data-type="text" data-value="{{ $person->occupation }}" @endif>{{ $person->occupation ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('Registered By') }}</span> <span class="font-medium" @if($canInlineEdit) data-edit="registered_by" data-type="text" data-value="{{ $person->registered_by }}" @endif>{{ $person->registered_by ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3 lg:col-span-4"><span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('Updated Date') }}</span>
                    <span class="font-medium">{{ $person->updated_at?->format('Y-m-d') ?? '-' }}</span>
                </div>
            </div>

            {{-- Death details --}}
            @if ($person->is_deceased)
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50/50 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-bold text-slate-900">{{ \App\Support\FrontendLocale::text('मृत्यु विवरण') }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-slate-900">
                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('मृत्यु मिति (A.D.)') }}</span>
                            <span class="font-medium" @if($canInlineEdit) data-edit="death_date" data-type="date" data-value="{{ $person->death_date?->format('Y-m-d') }}" @endif>{{ $person->death_date ? $person->death_date->format('Y-m-d') : '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('मृत्यु स्थान') }}</span>
                            <span class="font-medium" @if($canInlineEdit) data-edit="death_place" data-type="text" data-value="{{ $person->death_place }}" @endif>{{ $person->death_place ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('मृत्यु तिथी') }}</span>
                            <span class="font-medium" @if($canInlineEdit) data-edit="death_tithi" data-type="text" data-value="{{ $person->death_tithi }}" @endif>{{ $person->death_tithi ?? '-' }}</span>
                        </div>

                        <div class="lg:col-span-2">
                            <span class="text-slate-500 block text-xs mb-0.5">{{ \App\Support\FrontendLocale::text('मृत्युको कारण') }}</span>
                            <span class="font-medium" @if($canInlineEdit) data-edit="death_reason" data-type="text" data-value="{{ $person->death_reason }}" @endif>{{ $person->death_reason ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        {{-- Relations --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="text-lg font-bold mb-4 text-slate-900">{{ \App\Support\FrontendLocale::text('Relations') }}</div>

            <div class="text-sm space-y-5 text-slate-900">

                {{-- Parents --}}
                <div>
                    <div class="text-slate-500 text-xs font-semibold mb-2 uppercase tracking-wider">{{ \App\Support\FrontendLocale::text('Parents') }}</div>
                    <div class="space-y-2">
                        @forelse($person->parents as $pp)
                            <a class="group block px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white transition-all"
                               href="{{ \App\Support\FrontendLocale::route('member.page', $pp->id) }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium group-hover:text-blue-600 truncate">{{ $pp->display_name }}</span>
                                    <span class="text-xs text-slate-400">{{ $pp->member_no ?? '#'.$pp->id }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-slate-400 italic text-xs">{{ \App\Support\FrontendLocale::text('No records found') }}</div>

                            {{-- No parent linked: search an existing person and link (admin) / request (member) --}}
                            @auth
                            <form method="POST" action="{{ \App\Support\FrontendLocale::route('request.parent', $person->id) }}" id="linkParentForm"
                                  class="mt-1 rounded-xl border border-dashed border-teal-200 bg-teal-50/40 p-3">
                                @csrf
                                <label for="linkParentSearch" class="flex items-center gap-1.5 text-xs font-semibold text-teal-800">
                                    🔗 {{ $canInlineEdit ? \App\Support\FrontendLocale::text('अभिभावक जोड्नुहोस्') : \App\Support\FrontendLocale::text('अभिभावक जोड्ने अनुरोध') }}
                                </label>
                                <div class="relative mt-1.5">
                                    <input type="text" id="linkParentSearch" autocomplete="off" placeholder="{{ \App\Support\FrontendLocale::text('बुबा/आमाको नाम, ID वा सदस्य नं…') }}"
                                           class="w-full rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-100">
                                    <div id="linkParentResults" class="absolute left-0 right-0 z-20 mt-1 hidden max-h-60 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg"></div>
                                </div>
                                <input type="hidden" name="parent_id" id="linkParentId" value="{{ old('parent_id') }}">

                                <div id="linkParentChosen" class="mt-2 hidden rounded-lg border border-teal-200 bg-white p-2">
                                    <div class="flex items-center gap-2">
                                        <div class="min-w-0 flex-1">
                                            <div class="truncate text-sm font-semibold text-slate-800" data-lp="name"></div>
                                            <div class="truncate text-[11px] text-slate-400" data-lp="meta"></div>
                                        </div>
                                        <button type="button" id="linkParentClear" class="shrink-0 text-xs font-semibold text-rose-600 hover:underline">{{ \App\Support\FrontendLocale::text('हटाउनुहोस्') }}</button>
                                    </div>
                                    <div class="mt-2 flex flex-wrap items-center gap-2">
                                        <select name="relation_type" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs">
                                            <option value="birth">{{ \App\Support\FrontendLocale::text('जन्म (Birth)') }}</option>
                                            <option value="adoption">{{ \App\Support\FrontendLocale::text('धर्मपुत्र (Adoption)') }}</option>
                                        </select>
                                        @unless($canInlineEdit)
                                            <input type="text" name="note" maxlength="500" placeholder="{{ \App\Support\FrontendLocale::text('नोट (ऐच्छिक)') }}" class="min-w-0 flex-1 rounded-lg border border-slate-200 px-2 py-1 text-xs">
                                        @endunless
                                        <button type="submit" class="ml-auto rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-700">
                                            {{ $canInlineEdit ? \App\Support\FrontendLocale::text('जोड्नुहोस्') : \App\Support\FrontendLocale::text('अनुरोध पठाउनुहोस्') }}
                                        </button>
                                    </div>
                                </div>
                                @error('parent_id') <div class="mt-1 text-xs font-semibold text-rose-600">{{ $message }}</div> @enderror
                            </form>
                            <script>
                            (() => {
                                const input = document.getElementById('linkParentSearch');
                                const box = document.getElementById('linkParentResults');
                                const hidden = document.getElementById('linkParentId');
                                const chosen = document.getElementById('linkParentChosen');
                                const blocked = new Set(@json(array_map('strval', $blockedParentIds ?? [])));
                                const url = @json(\App\Support\FrontendLocale::route('people.search'));
                                const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
                                let timer;

                                function pick(p) {
                                    hidden.value = p.id;
                                    chosen.querySelector('[data-lp=name]').textContent = p.display_name;
                                    chosen.querySelector('[data-lp=meta]').textContent = [p.member_number || '#' + p.id, p.pusta ? 'पु.' + p.pusta : null, p.gender, p.father_name ? 'बुबा: ' + p.father_name : null].filter(Boolean).join(' · ');
                                    chosen.classList.remove('hidden');
                                    box.classList.add('hidden');
                                    input.value = '';
                                }
                                document.getElementById('linkParentClear').addEventListener('click', () => { hidden.value = ''; chosen.classList.add('hidden'); input.focus(); });

                                input.addEventListener('input', () => {
                                    clearTimeout(timer);
                                    const term = input.value.trim();
                                    if (!term) { box.classList.add('hidden'); return; }
                                    timer = setTimeout(async () => {
                                        const rows = (await (await fetch(`${url}${url.includes('?') ? '&' : '?'}term=${encodeURIComponent(term)}`)).json()) || [];
                                        const list = rows.filter(p => !blocked.has(String(p.id)));
                                        const hiddenCount = rows.length - list.length;
                                        box.innerHTML = (list.length ? list.map((p, i) => `
                                            <button type="button" data-i="${i}" class="block w-full border-b border-slate-100 px-3 py-2 text-left last:border-0 hover:bg-teal-50">
                                                <div class="text-sm font-semibold text-slate-800">${esc(p.display_name)}${p.display_name_np ? ` <span class="font-normal text-slate-400">/ ${esc(p.display_name_np)}</span>` : ''}</div>
                                                <div class="text-[11px] text-slate-400">${esc([p.member_number || '#' + p.id, p.pusta ? 'पु.' + p.pusta : null, p.gender, p.father_name ? 'बुबा: ' + p.father_name : null].filter(Boolean).join(' · '))}</div>
                                            </button>`).join('') : '<div class="px-3 py-2 text-xs text-slate-400">भेटिएन।</div>')
                                            + (hiddenCount ? `<div class="bg-amber-50 px-3 py-1.5 text-[11px] text-amber-700">${hiddenCount} जना लुकाइयो — आफैं वा आफ्नै सन्तान अभिभावक हुन मिल्दैन।</div>` : '');
                                        box.classList.remove('hidden');
                                        box.querySelectorAll('[data-i]').forEach(b => b.addEventListener('click', () => pick(list[+b.dataset.i])));
                                    }, 220);
                                });
                                document.addEventListener('click', e => { if (!input.contains(e.target) && !box.contains(e.target)) box.classList.add('hidden'); });
                            })();
                            </script>
                            @else
                            <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}" class="mt-1 inline-block text-xs font-semibold text-teal-700 hover:underline">
                                {{ \App\Support\FrontendLocale::text('🔗 अभिभावक जोड्न लगइन गर्नुहोस्') }}
                            </a>
                            @endauth
                        @endforelse
                    </div>
                </div>

                {{-- Spouses --}}
                <div>
                    @php
                        $label = 'Spouses';
                        if(($person->gender ?? '') === 'male') $label = 'Wife/Wives';
                        elseif(($person->gender ?? '') === 'female') $label = 'Husband(s)';
                    @endphp

                    <div class="text-slate-500 text-xs font-semibold mb-2 uppercase tracking-wider">{{ $label }}</div>
                    <div class="space-y-2">
                        @forelse($spouses as $s)
                            <a class="group block px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white transition-all"
                               href="{{ \App\Support\FrontendLocale::route('member.page', $s->id) }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium group-hover:text-blue-600 truncate">{{ $s->display_name }}</span>
                                    <span class="text-xs text-slate-400">{{ $s->member_no ?? '#'.$s->id }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-slate-400 italic text-xs">{{ \App\Support\FrontendLocale::text('No records found') }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Children: sons and daughters in separate columns --}}
                @php
                    $allChildren = $children ?? $person->children;
                    $childColumns = [
                        ['title' => \App\Support\FrontendLocale::text('छोरा'), 'head' => 'bg-blue-50 text-blue-700 border-blue-100', 'num' => 'bg-blue-600', 'items' => $allChildren->where('gender', 'male')],
                        ['title' => \App\Support\FrontendLocale::text('छोरी'), 'head' => 'bg-pink-50 text-pink-700 border-pink-100', 'num' => 'bg-pink-500', 'items' => $allChildren->where('gender', 'female')],
                    ];
                    $otherChildren = $allChildren->whereNotIn('gender', ['male', 'female']);
                    if ($otherChildren->isNotEmpty()) {
                        $childColumns[] = ['title' => \App\Support\FrontendLocale::text('सन्तान'), 'head' => 'bg-slate-50 text-slate-600 border-slate-100', 'num' => 'bg-violet-600', 'items' => $otherChildren];
                    }
                @endphp
                <div>
                    <div class="text-slate-500 text-xs font-semibold mb-2 uppercase tracking-wider">
                        Children ({{ $allChildren->count() }})
                    </div>
                    @if($allChildren->isEmpty())
                        <div class="text-slate-400 italic text-xs">{{ \App\Support\FrontendLocale::text('No records found') }}</div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($childColumns as $col)
                                <div class="min-w-0">
                                    <div class="mb-2 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $col['head'] }}">
                                        {{ $col['title'] }} ({{ \App\Support\FrontendLocale::number($col['items']->count()) }})
                                    </div>
                                    <div class="space-y-2">
                                        @forelse($col['items'] as $cc)
                                            <a class="group flex items-center gap-3 px-3 py-2 rounded-xl border border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white transition-all"
                                               href="{{ \App\Support\FrontendLocale::route('member.page', $cc->id) }}">
                                                <span class="flex-none w-6 h-6 rounded-full {{ $col['num'] }} text-white text-[11px] font-bold flex items-center justify-center">
                                                    {{ $cc->birth ? \App\Support\FrontendLocale::number($cc->birth['rank']) : '•' }}
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block font-medium group-hover:text-blue-600 truncate">{{ $cc->display_name }}</span>
                                                    <span class="block text-[11px] text-slate-400 truncate">
                                                        {{ collect([\App\Support\FrontendLocale::birthLabel($cc->birth['word'] ?? null), $cc->member_no ?? '#'.$cc->id])->filter()->implode(' · ') }}
                                                    </span>
                                                </span>
                                            </a>
                                        @empty
                                            <div class="text-slate-400 italic text-xs px-1">—</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Bio --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 mt-5 shadow-sm">
        <div class="text-lg font-bold mb-3 text-slate-900">{{ \App\Support\FrontendLocale::text('जीवनी') }}</div>
        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed" @if($canInlineEdit) data-edit="bio" data-type="textarea" data-value="{{ $person->bio }}" @endif>{{ $person->bio ?: 'No biography available.' }}</div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3 mt-5">
        @if (!$person->is_deceased)
            @auth
            <button onclick="document.getElementById('deathModal').style.display='flex'"
                    class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
                {{ \App\Support\FrontendLocale::text('Claim Dead') }}
            </button>
            @else
            <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}"
               class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
                {{ \App\Support\FrontendLocale::text('Login to Claim Dead') }}
            </a>
            @endauth
        @endif

        @auth
        <button onclick="document.getElementById('childModal').style.display='flex'"
                class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm hover:bg-slate-800 font-semibold shadow-sm transition-colors">
            {{ \App\Support\FrontendLocale::text('Add Child') }}
        </button>
        @else
        <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm hover:bg-slate-800 font-semibold shadow-sm transition-colors">
            {{ \App\Support\FrontendLocale::text('Login to Add Child') }}
        </a>
        @endauth

        @auth
        <button onclick="document.getElementById('editModal').style.display='flex'"
                class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
            {{ \App\Support\FrontendLocale::text('Edit Profile') }}
        </button>
        @else
        <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
            {{ \App\Support\FrontendLocale::text('Login to Edit Profile') }}
        </a>
        @endauth

        @auth
        <button onclick="document.getElementById('marriageModal').style.display='flex'"
                class="px-4 py-2 bg-white border border-blue-300 shadow-sm rounded-xl text-sm hover:bg-blue-50 text-blue-800 font-semibold transition-colors">
            {{ \App\Support\FrontendLocale::text('💍 विवाह थप्नुहोस्') }}
        </button>
        @else
        <a href="{{ \App\Support\FrontendLocale::route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-white border border-blue-300 shadow-sm rounded-xl text-sm hover:bg-blue-50 text-blue-800 font-semibold transition-colors">
            {{ \App\Support\FrontendLocale::text('Login to Add Marriage') }}
        </a>
        @endauth
    </div>

    {{-- ========================= DEATH MODAL ========================= --}}
    <div id="deathModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">
        
        {{-- Modal Container --}}
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl flex flex-col max-h-[90vh] modal-panel">
            
            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <h2 class="text-lg font-bold text-slate-900">{{ \App\Support\FrontendLocale::text('मृत्यु सुतक जानकारी') }}</h2>
                <button type="button" 
                        onclick="document.getElementById('deathModal').style.display='none'" 
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form method="POST" action="{{ \App\Support\FrontendLocale::route('request.death', $person->id) }}" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="form_type" value="death">

                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-900">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('मृत्यु मिति (A.D.)') }}</label>
                            <input type="date" name="death_date_ad" value="{{ old('death_date_ad') }}" class="inputCompact">
                            @error('death_date_ad') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('मृत्यु मिति (B.S.)') }}</label>
                            <input type="text" name="death_date_bs" value="{{ old('death_date_bs') }}" placeholder="2081-01-15" class="inputCompact">
                            @error('death_date_bs') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('मृत्यु स्थान') }}</label>
                            <input type="text" name="death_place" value="{{ old('death_place') }}" class="inputCompact">
                            @error('death_place') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('मृत्यु तिथि') }}</label>
                            <input type="text" name="death_tithi" value="{{ old('death_tithi') }}" class="inputCompact">
                            @error('death_tithi') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('मृत्युको कारण') }}</label>
                            <textarea name="death_reason" class="textareaCompact">{{ old('death_reason') }}</textarea>
                            @error('death_reason') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        @guest
                        <div class="sm:col-span-2 pt-2">
                            <label class="block font-medium text-slate-700 mb-1">{{ \App\Support\FrontendLocale::text('तपाईंको नाम') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="submitted_name" value="{{ old('submitted_name') }}" class="inputCompact bg-slate-50">
                            @error('submitted_name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        @endguest
                    </div>
                </div>

                {{-- Footer (Fixed) --}}
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0 rounded-b-2xl">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl w-full font-semibold shadow-sm transition-colors">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            {{ \App\Support\FrontendLocale::text('सिधै सेभ गर्नुहोस्') }}
                        @else
                            {{ \App\Support\FrontendLocale::text('अनुरोध पठाउनुहोस्') }}
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================= CHILD MODAL ========================= --}}
    @php
        $T = fn ($s) => \App\Support\FrontendLocale::text($s);
        $isChildOld = old('form_type') === 'child';
        $co = fn ($k, $d = null) => $isChildOld ? old($k, $d) : $d;   // old input only from this form
        $parentPustaNum = (int) strtr((string) $person->pusta, array_combine(preg_split('//u', '०१२३४५६७८९', -1, PREG_SPLIT_NO_EMPTY), range(0, 9)));
        $childPusta = $parentPustaNum ? \App\Support\FrontendLocale::number($parentPustaNum + 1) : null;
        $kidsNow = ($children ?? $person->children);
        $parentIsMale = ($person->gender ?? '') === 'male';
        $childMoreOpen = $isChildOld && collect(['lineage','family_type','blood_group','rashifal','religion','special_note','bio'])->contains(fn ($f) => $errors->has($f) || filled(old($f)));
    @endphp
    <div id="childModal" style="display:none" class="fixed inset-0 z-[80] items-end sm:items-center justify-center bg-slate-900/45 backdrop-blur-sm p-0 sm:p-6 modal-backdrop">
        <div class="flex w-full sm:max-w-3xl max-h-[94vh] flex-col overflow-hidden rounded-t-2xl sm:rounded-2xl border border-slate-200 bg-white shadow-2xl modal-panel">

            {{-- Header: whose child is being added --}}
            <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50 to-emerald-50 px-4 sm:px-5 py-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-xl ring-1 ring-sky-100">👶</div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[11px] font-semibold uppercase tracking-wide text-sky-500">
                            {{ $T($canInlineEdit ? 'नयाँ सन्तान थप्नुहोस्' : 'नयाँ सन्तान जानकारी अनुरोध') }}
                        </div>
                        <div class="font-bold text-slate-900 truncate">{{ $person->display_name }} {{ $T('को सन्तान') }}</div>
                    </div>
                    <button type="button" onclick="document.getElementById('childModal').style.display='none'"
                            class="h-8 w-8 shrink-0 rounded-full text-lg leading-none text-slate-500 hover:bg-white/70" aria-label="{{ $T(\App\Support\FrontendLocale::text('बन्द गर्नुहोस्')) }}">×</button>
                </div>

                {{-- Guardian card --}}
                <div class="mt-3 flex flex-wrap items-center gap-3 rounded-xl border border-white bg-white/80 p-2.5 shadow-sm">
                    <div class="h-11 w-11 shrink-0 overflow-hidden rounded-full bg-slate-100 ring-1 ring-slate-200 flex items-center justify-center text-xl">
                        @if(!empty($person->photo_path) && file_exists(public_path($person->photo_path)))
                            <img src="{{ asset($person->photo_path) }}" class="h-full w-full object-cover" alt="">
                        @else
                            {{ $parentIsMale ? '👨' : (($person->gender ?? '') === 'female' ? '👩' : '🧑') }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $T(\App\Support\FrontendLocale::text('अभिभावक')) }} · {{ $T($parentIsMale ? \App\Support\FrontendLocale::text('बुबा') : (($person->gender ?? '') === 'female' ? \App\Support\FrontendLocale::text('आमा') : \App\Support\FrontendLocale::text('अभिभावक'))) }}</div>
                        <div class="truncate text-sm font-bold text-slate-900">{{ $person->display_name }}</div>
                        <div class="truncate text-[11px] text-slate-500">
                            {{ $person->member_no ?? '#'.$person->id }}@if($person->pusta) · {{ $T(\App\Support\FrontendLocale::text('पु.')) }}{{ $person->pusta }}@endif
                            @if(($spouses ?? collect())->isNotEmpty()) · {{ $T($parentIsMale ? \App\Support\FrontendLocale::text('आमा') : 'जीवनसाथी') }}: {{ $spouses->pluck('display_name')->implode(', ') }}@endif
                        </div>
                    </div>
                    <div class="flex w-full flex-wrap gap-1.5 text-[11px] font-semibold sm:w-auto">
                        @if($childPusta)
                            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-emerald-700">{{ $T('सन्तानको पुस्ता') }}: {{ $childPusta }}</span>
                        @endif
                        <span class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-blue-700">{{ $T(\App\Support\FrontendLocale::text('छोरा')) }} {{ \App\Support\FrontendLocale::number($kidsNow->where('gender', 'male')->count()) }}</span>
                        <span class="rounded-full border border-pink-200 bg-pink-50 px-2 py-0.5 text-pink-700">{{ $T(\App\Support\FrontendLocale::text('छोरी')) }} {{ \App\Support\FrontendLocale::number($kidsNow->where('gender', 'female')->count()) }}</span>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ \App\Support\FrontendLocale::route('request.child', $person->id) }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <input type="hidden" name="form_type" value="child">

                <div class="min-h-0 flex-1 space-y-5 overflow-y-auto px-4 sm:px-5 py-4 text-sm text-slate-900">

                    {{-- Photo + names --}}
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex shrink-0 flex-col items-center gap-2 sm:w-40">
                            <div id="childPhotoPreview"
                                 class="flex h-24 w-24 items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-slate-300 bg-slate-50 text-3xl text-slate-300">👶</div>
                            <div class="flex w-full flex-wrap items-start justify-center gap-x-1.5">
                                <label for="childPhotoInput" class="mt-1.5 inline-flex cursor-pointer items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50">🖼 {{ $T('फाइल') }}</label>
                                <input type="file" name="photo" id="childPhotoInput" accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="sr-only js-photo-limit" data-camera data-camera-label="📷 {{ $T(\App\Support\FrontendLocale::text('क्यामेरा')) }}">
                                <p class="mt-1 w-full text-center text-[10px] text-slate-400">{{ $T(\App\Support\FrontendLocale::text('Max 500 KB')) }}</p>
                            </div>
                            @error('photo') <div class="err text-center">{{ $message }}</div> @enderror
                        </div>

                        <div class="min-w-0 flex-1 space-y-3">
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('नेपाली नाम')) }} <span class="text-red-500">*</span></label>
                                <input name="display_name" value="{{ $co('display_name') }}" placeholder="{{ $T('सन्तानको पूरा नाम') }}" class="inputCompact">
                                @if($isChildOld) @error('display_name') <div class="err">{{ $message }}</div> @enderror @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Full Name (English)')) }}</label>
                                    <input name="display_name_np" value="{{ $co('display_name_np') }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Limbu Name / ᤕᤰᤌᤢᤱ नाम')) }}</label>
                                    <input name="display_name_limbu" value="{{ $co('display_name_limbu') }}" class="inputCompact">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('लिङ्ग')) }}</label>
                                    @php $g = $co('gender', 'male'); @endphp
                                    <select name="gender" id="childGenderSelect" class="inputCompact bg-white">
                                        <option value="male" @selected($g==='male')>{{ $T('छोरा (Male)') }}</option>
                                        <option value="female" @selected($g==='female')>{{ $T('छोरी (Female)') }}</option>
                                        <option value="other" @selected($g==='other')>{{ $T(\App\Support\FrontendLocale::text('अन्य')) }}</option>
                                        <option value="unknown" @selected($g==='unknown')>{{ $T(\App\Support\FrontendLocale::text('थाहा छैन')) }}</option>
                                    </select>
                                    @if($isChildOld) @error('gender') <div class="err">{{ $message }}</div> @enderror @endif
                                </div>
                                <div>
                                    <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('सन्तान क्रम (')) }}<span id="childOrderRelation">{{ $T(\App\Support\FrontendLocale::text('सन्तान')) }}</span>)</label>
                                    <select name="birth_order" id="childBirthOrder" class="inputCompact bg-white" data-old="{{ $co('birth_order') }}"></select>
                                    <p class="mt-0.5 truncate text-[10px] text-slate-400" id="childOrderTaken"></p>
                                    @if($isChildOld) @error('birth_order') <div class="err">{{ $message }}</div> @enderror @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Birth --}}
                    <div>
                        <div class="editSection">{{ $T(\App\Support\FrontendLocale::text('जन्म')) }}</div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Birth Date (A.D.)')) }}</label>
                                <input type="date" name="birth_date" value="{{ $co('birth_date') }}" class="inputCompact">
                                @if($isChildOld) @error('birth_date') <div class="err">{{ $message }}</div> @enderror @endif
                            </div>
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Birth Date (B.S.)')) }}</label>
                                <input name="birth_date_bs" value="{{ $co('birth_date_bs') }}" placeholder="2081-01-15" class="inputCompact">
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Birth Place')) }}</label>
                                <input name="birth_place" value="{{ $co('birth_place') }}" class="inputCompact">
                            </div>
                        </div>
                    </div>

                    {{-- Contact / education --}}
                    <div>
                        <div class="editSection">{{ $T('सम्पर्क र पेशा') }}</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Mobile Number')) }}</label>
                                <input name="mobile" value="{{ $co('mobile') }}" inputmode="tel" class="inputCompact">
                            </div>
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Email Address')) }}</label>
                                <input type="email" name="email" value="{{ $co('email') }}" class="inputCompact">
                                @if($isChildOld) @error('email') <div class="err">{{ $message }}</div> @enderror @endif
                            </div>
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Education')) }}</label>
                                <input name="education" value="{{ $co('education') }}" class="inputCompact">
                            </div>
                            <div>
                                <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Occupation')) }}</label>
                                <input name="occupation" value="{{ $co('occupation') }}" class="inputCompact">
                            </div>
                        </div>
                    </div>

                    {{-- Less-used fields, folded away --}}
                    <details class="group rounded-xl border border-slate-200 bg-slate-50/60" @if($childMoreOpen) open @endif>
                        <summary class="flex cursor-pointer list-none items-center gap-2 px-3 py-2.5 text-xs font-bold uppercase tracking-wide text-slate-500">
                            <span class="transition-transform group-open:rotate-90">▸</span> {{ $T(\App\Support\FrontendLocale::text('थप विवरण')) }}
                            <span class="font-normal normal-case text-slate-400">— {{ $T('रक्त समूह, राशि, धर्म, वंश, नोट…') }}</span>
                        </summary>
                        <div class="space-y-3 border-t border-slate-200 bg-white px-3 py-3 rounded-b-xl">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Blood Group')) }}</label><input name="blood_group" value="{{ $co('blood_group') }}" placeholder="O+" class="inputCompact"></div>
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Rashifal')) }}</label><input name="rashifal" value="{{ $co('rashifal') }}" class="inputCompact"></div>
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Religion')) }}</label><input name="religion" value="{{ $co('religion') }}" class="inputCompact"></div>
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Lineage')) }}</label><input name="lineage" value="{{ $co('lineage') }}" class="inputCompact"></div>
                                <div class="col-span-2"><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Family Type')) }}</label><input name="family_type" value="{{ $co('family_type') }}" class="inputCompact"></div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Special Note')) }}</label><textarea name="special_note" rows="3" class="textareaCompact">{{ $co('special_note') }}</textarea></div>
                                <div><label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('Biography')) }}</label><textarea name="bio" rows="3" class="textareaCompact">{{ $co('bio') }}</textarea></div>
                            </div>
                        </div>
                    </details>

                    @guest
                    <div>
                        <label class="editLabel">{{ $T(\App\Support\FrontendLocale::text('तपाईंको नाम (Submitter)')) }} <span class="text-red-500">*</span></label>
                        <input name="submitted_name" value="{{ $co('submitted_name') }}" class="inputCompact max-w-sm">
                        @if($isChildOld) @error('submitted_name') <div class="err">{{ $message }}</div> @enderror @endif
                    </div>
                    @endguest
                </div>

                {{-- Footer --}}
                <div class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/80 px-4 sm:px-5 py-3">
                    <span class="hidden sm:block truncate text-[11px] text-slate-400">
                        {{ $person->display_name }} {{ $T('को सन्तानको रूपमा') }} {{ $T($canInlineEdit ? \App\Support\FrontendLocale::text('सिधै थपिन्छ।') : \App\Support\FrontendLocale::text('Admin को स्वीकृतिपछि थपिन्छ।')) }}
                    </span>
                    <button type="button" onclick="document.getElementById('childModal').style.display='none'"
                            class="ml-auto rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">{{ $T(\App\Support\FrontendLocale::text('रद्द')) }}</button>
                    <button type="submit" class="rounded-lg bg-sky-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-sky-700">
                        {{ $T($canInlineEdit ? '👶 सन्तान थप्नुहोस्' : \App\Support\FrontendLocale::text('अनुरोध पठाउनुहोस्')) }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // child photo preview (file or camera)
    (() => {
        const input = document.getElementById('childPhotoInput');
        const preview = document.getElementById('childPhotoPreview');
        if (!input || !preview) return;
        const empty = preview.className;
        input.addEventListener('change', () => {
            const f = input.files?.[0];
            if (!f || f.size > 500 * 1024) { preview.className = empty; preview.textContent = '👶'; return; } // too big: the size check clears it
            preview.className = 'h-24 w-24 overflow-hidden rounded-full border-2 border-sky-200 bg-white';
            preview.innerHTML = `<img src="${URL.createObjectURL(f)}" alt="" class="h-full w-full object-cover">`;
        });
    })();
    </script>

</div>

    {{-- ========================= EDIT PROFILE MODAL ========================= --}}
    @php
        $isAdminUser = auth()->check() && auth()->user()->isAdmin();
        // which tab holds each field, so the tab with a validation error opens first
        $editTabs = [
            'personal' => ['display_name','display_name_np','display_name_limbu','gender','pusta','birth_order','blood_group','rashifal','religion','photo'],
            'contact'  => ['birth_date','birth_date_bs','birth_place','mobile','email','address','education','occupation'],
            'more'     => ['member_type','membership','registered_by','death_date','death_place','death_tithi','death_reason','special_note','bio','submitted_note','submitted_name'],
        ];
        $editErrTab = old('form_type') === 'edit'
            ? collect($editTabs)->search(fn ($fields) => collect($fields)->contains(fn ($f) => $errors->has($f)))
            : false;
    @endphp
    <div id="editModal" style="display:none" class="fixed inset-0 z-[80] flex items-center justify-center p-3 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[92vh] modal-panel"
             x-data="{ tab: @js($editErrTab ?: 'personal') }">

            {{-- Header --}}
            <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-100 shrink-0">
                <div class="w-10 h-10 rounded-full overflow-hidden bg-slate-100 flex items-center justify-center text-lg shrink-0 ring-1 ring-slate-200">
                    @if(!empty($person->photo_path) && file_exists(public_path($person->photo_path)))
                        <img src="{{ asset($person->photo_path) }}" class="w-full h-full object-cover" alt="">
                    @else
                        {{ ($person->gender ?? '') === 'female' ? '👩' : (($person->gender ?? '') === 'male' ? '👨' : '🧑') }}
                    @endif
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-bold text-slate-900 truncate">{{ $isAdminUser ? \App\Support\FrontendLocale::text('प्रोफाइल सम्पादन') : \App\Support\FrontendLocale::text('प्रोफाइल सम्पादन अनुरोध') }} — {{ $person->display_name }}</h2>
                    <p class="text-[11px] text-slate-500">
                        {{ $person->member_no ?? '#'.$person->id }}@if($person->pusta) · पु.{{ $person->pusta }}@endif ·
                        {{ $isAdminUser ? \App\Support\FrontendLocale::text('परिवर्तन सिधै लागू हुन्छ।') : \App\Support\FrontendLocale::text('परिवर्तन गर्न चाहेका फिल्ड मात्र बदल्नुहोस्।') }}
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                        class="ml-auto p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full" aria-label="{{ \App\Support\FrontendLocale::text('बन्द गर्नुहोस्') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex gap-1 px-5 pt-2 border-b border-slate-100 shrink-0 overflow-x-auto overflow-y-hidden">
                @foreach (['personal' => \App\Support\FrontendLocale::text('व्यक्तिगत'), 'contact' => \App\Support\FrontendLocale::text('जन्म र सम्पर्क'), 'more' => \App\Support\FrontendLocale::text('थप विवरण')] as $key => $label)
                    <button type="button" @click="tab = '{{ $key }}'"
                        class="relative whitespace-nowrap px-3 py-2 text-sm font-semibold border-b-2 -mb-px transition-colors"
                        :class="tab === '{{ $key }}' ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-700'">
                        {{ $label }}
                        @if(old('form_type') === 'edit' && collect($editTabs[$key])->contains(fn ($f) => $errors->has($f)))
                            <span class="absolute top-1.5 right-0.5 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        @endif
                    </button>
                @endforeach
            </div>

            <form method="POST" action="{{ \App\Support\FrontendLocale::route('request.edit', $person->id) }}" enctype="multipart/form-data" class="flex flex-col min-h-0 flex-1">
                @csrf
                <input type="hidden" name="form_type" value="edit">

                <div class="px-5 py-4 overflow-y-auto min-h-0 flex-1 text-sm text-slate-900">

                    {{-- ── Tab 1: personal ── --}}
                    <div x-show="tab === 'personal'" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('नेपाली नाम') }} <span class="text-red-500">*</span></label>
                                <input name="display_name" value="{{ old('display_name', $person->display_name) }}" class="inputCompact">
                                @error('display_name') <div class="err">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('Full Name (English)') }}</label>
                                <input name="display_name_np" value="{{ old('display_name_np', $person->display_name_np) }}" class="inputCompact">
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('Limbu / ᤕᤰᤌᤢᤱ नाम') }}</label>
                                <input name="display_name_limbu" value="{{ old('display_name_limbu', $person->display_name_limbu) }}" class="inputCompact">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('लिङ्ग') }}</label>
                                @php $eg = old('gender', $person->gender ?? 'unknown'); @endphp
                                <select name="gender" class="inputCompact bg-white">
                                    @foreach (['male' => \App\Support\FrontendLocale::text('पुरुष (Male)'), 'female' => \App\Support\FrontendLocale::text('महिला (Female)'), 'other' => \App\Support\FrontendLocale::text('अन्य (Other)'), 'unknown' => \App\Support\FrontendLocale::text('थाहा छैन')] as $k => $v)
                                        <option value="{{ $k }}" @selected($eg === $k)>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('पुस्ता') }}</label>
                                <input name="pusta" value="{{ old('pusta', $person->pusta) }}" placeholder="२८" class="inputCompact">
                            </div>
                            @if($selfOrder)
                            <div class="col-span-2 sm:col-span-1">
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('सन्तान क्रम') }}</label>
                                @php $eo = (int) old('birth_order', $selfOrder['current']); @endphp
                                <select name="birth_order" class="inputCompact bg-white">
                                    <option value="">—</option>
                                    @foreach (\App\Support\SiblingOrder::options() as $o)
                                        <option value="{{ $o['value'] }}" @selected($eo === $o['value'])>{{ $o['label'] }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-slate-400 mt-0.5 truncate" title="{{ collect($selfOrder['others'])->map(fn ($n, $r) => \App\Support\FrontendLocale::number($r).' '.$n)->implode(', ') }}">
                                    {{ $selfOrder['parent'] }} का{{ $selfOrder['others'] ? \App\Support\FrontendLocale::text(' · अरू: ').collect($selfOrder['others'])->map(fn ($n, $r) => \App\Support\FrontendLocale::number($r).' '.$n)->implode(', ') : '' }}
                                </p>
                                @error('birth_order') <div class="err">{{ $message }}</div> @enderror
                            </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('रक्त समूह') }}</label>
                                <input name="blood_group" value="{{ old('blood_group', $person->blood_group) }}" placeholder="O+" class="inputCompact">
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('राशि') }}</label>
                                <input name="rashifal" value="{{ old('rashifal', $person->rashifal) }}" class="inputCompact">
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('धर्म') }}</label>
                                <input name="religion" value="{{ old('religion', $person->religion) }}" class="inputCompact">
                            </div>
                        </div>

                        <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/60 p-3">
                            <label class="editLabel">{{ \App\Support\FrontendLocale::text('प्रोफाइल फोटो') }}</label>
                            <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp" class="inputCompact js-photo-limit bg-white" data-camera>
                            <p class="text-[10px] text-slate-400 mt-1">{{ \App\Support\FrontendLocale::text('Max 500 KB · JPEG / PNG / WebP') }}{{ $isAdminUser ? '' : \App\Support\FrontendLocale::text(' · अनुरोधको रूपमा पठाइन्छ') }}</p>
                            @error('photo') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- ── Tab 2: birth & contact ── --}}
                    <div x-show="tab === 'contact'" x-cloak class="space-y-4">
                        <div>
                            <div class="editSection">{{ \App\Support\FrontendLocale::text('जन्म') }}</div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('जन्म मिति (A.D.)') }}</label>
                                    <input type="date" name="birth_date" value="{{ old('birth_date', $person->birth_date?->format('Y-m-d')) }}" class="inputCompact">
                                    @error('birth_date') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('जन्म मिति (B.S.)') }}</label>
                                    <input name="birth_date_bs" value="{{ old('birth_date_bs', $person->birth_date_bs) }}" placeholder="2081-01-15" class="inputCompact">
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('जन्मस्थान') }}</label>
                                    <input name="birth_place" value="{{ old('birth_place', $person->birth_place) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="editSection">{{ \App\Support\FrontendLocale::text('सम्पर्क') }}</div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('मोबाइल') }}</label>
                                    <input name="mobile" value="{{ old('mobile', $person->mobile) }}" inputmode="tel" class="inputCompact">
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('इमेल') }}</label>
                                    <input type="email" name="email" value="{{ old('email', $person->email) }}" class="inputCompact">
                                    @error('email') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('ठेगाना') }}</label>
                                    <input name="address" value="{{ old('address', $person->address) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="editSection">{{ \App\Support\FrontendLocale::text('शिक्षा र पेशा') }}</div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('शिक्षा') }}</label>
                                    <input name="education" value="{{ old('education', $person->education) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('पेशा') }}</label>
                                    <input name="occupation" value="{{ old('occupation', $person->occupation) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Tab 3: more ── --}}
                    <div x-show="tab === 'more'" x-cloak class="space-y-4">
                        @if($isAdminUser)
                        <div>
                            <div class="editSection">{{ \App\Support\FrontendLocale::text('सदस्यता') }} <span class="normal-case font-normal text-slate-400">{{ \App\Support\FrontendLocale::text('(Admin)') }}</span></div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('सदस्यको प्रकार') }}</label>
                                    @php $emt = old('member_type', $person->member_type); @endphp
                                    <select name="member_type" class="inputCompact bg-white">
                                        <option value="">—</option>
                                        @foreach(\App\Support\MemberType::all() as $type)
                                            <option value="{{ $type }}" @selected($emt === $type)>{{ \App\Support\FrontendLocale::text($type) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('सदस्यता') }}</label>
                                    <input name="membership" value="{{ old('membership', $person->membership) }}" class="inputCompact">
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('Registered By') }}</label>
                                    <input name="registered_by" value="{{ old('registered_by', $person->registered_by) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($person->is_deceased)
                        <div>
                            <div class="editSection text-red-500">{{ \App\Support\FrontendLocale::text('मृत्यु विवरण') }}</div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('मृत्यु मिति (A.D.)') }}</label>
                                    <input type="date" name="death_date" value="{{ old('death_date', $person->death_date?->format('Y-m-d')) }}" class="inputCompact">
                                    @error('death_date') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('मृत्यु स्थान') }}</label>
                                    <input name="death_place" value="{{ old('death_place', $person->death_place) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('मृत्यु तिथि') }}</label>
                                    <input name="death_tithi" value="{{ old('death_tithi', $person->death_tithi) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="editLabel">{{ \App\Support\FrontendLocale::text('मृत्युको कारण') }}</label>
                                    <input name="death_reason" value="{{ old('death_reason', $person->death_reason) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('जीवनी') }}</label>
                                <textarea name="bio" rows="4" class="textareaCompact">{{ old('bio', $person->bio) }}</textarea>
                            </div>
                            <div>
                                <label class="editLabel">{{ \App\Support\FrontendLocale::text('विशेष नोट') }}</label>
                                <textarea name="special_note" rows="4" class="textareaCompact">{{ old('special_note', $person->special_note) }}</textarea>
                            </div>
                        </div>

                        @unless($isAdminUser)
                        <div>
                            <label class="editLabel">{{ \App\Support\FrontendLocale::text('परिवर्तनको कारण / नोट') }}</label>
                            <input name="submitted_note" value="{{ old('submitted_note') }}" placeholder="{{ \App\Support\FrontendLocale::text('किन परिवर्तन चाहनुभयो?') }}" class="inputCompact">
                        </div>
                        @endunless

                        @guest
                        <div>
                            <label class="editLabel">{{ \App\Support\FrontendLocale::text('तपाईंको नाम (Submitter)') }} <span class="text-red-500">*</span></label>
                            <input name="submitted_name" value="{{ old('submitted_name') }}" class="inputCompact max-w-sm">
                            @error('submitted_name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        @endguest
                    </div>
                </div>

                {{-- Footer --}}
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/70 shrink-0 rounded-b-2xl flex items-center gap-3">
                    <div class="hidden sm:flex items-center gap-1 text-[11px] text-slate-400">
                        <template x-for="(t, i) in ['personal','contact','more']" :key="t">
                            <span class="w-1.5 h-1.5 rounded-full" :class="tab === t ? 'bg-blue-600' : 'bg-slate-300'"></span>
                        </template>
                        <span class="ml-1">{{ \App\Support\FrontendLocale::text('सबै ट्याबका परिवर्तन एकैपटक सेभ हुन्छन्') }}</span>
                    </div>
                    <button type="button" onclick="document.getElementById('editModal').style.display='none'"
                            class="ml-auto px-4 py-2 rounded-lg text-sm font-semibold text-slate-600 hover:bg-slate-100">
                        {{ \App\Support\FrontendLocale::text('रद्द') }}
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm">
                        {{ $isAdminUser ? \App\Support\FrontendLocale::text('सेभ गर्नुहोस्') : \App\Support\FrontendLocale::text('अनुरोध पठाउनुहोस्') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================= MARRIAGE MODAL ========================= --}}
    @include('partials.marriage-form', ['marriagePerson' => $person])

{{-- Auto open modal if errors exist --}}
@if ($errors->any())
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formType = @json(old('form_type'));
    const deathModal = document.getElementById('deathModal');
    const childModal = document.getElementById('childModal');
    const editModal = document.getElementById('editModal');
    const marriageModal = document.getElementById('marriageModal');

    if (formType === 'death' && deathModal) deathModal.style.display = 'flex';
    else if (formType === 'child' && childModal) childModal.style.display = 'flex';
    else if (formType === 'edit' && editModal) editModal.style.display = 'flex';
    else if (formType === 'marriage' && marriageModal) marriageModal.style.display = 'flex';
});

// Close modals on Escape key press
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        ['deathModal','childModal','editModal','marriageModal'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
    }
});
</script>
@endif

<script>

// सन्तान क्रम for the add-child form: free places depend on the chosen gender
(() => {
    const gender = document.getElementById('childGenderSelect');
    const select = document.getElementById('childBirthOrder');
    if (!gender || !select) return;
    const taken = @json($takenOrders ?? []);
    const RELATION = { male: @js(\App\Support\FrontendLocale::text('छोरा')), female: @js(\App\Support\FrontendLocale::text('छोरी')) };
    const np = n => @js(\App\Support\FrontendLocale::locale()) === 'en' ? String(n) : String(n).replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
    const MAX = {{ \App\Support\SiblingOrder::MAX }};
    let wanted = parseInt(select.dataset.old, 10) || null;

    // १–१० always; default = the number after the existing children of that gender
    function fill() {
        const g = gender.value;
        const used = taken[g] || {};
        const nums = Object.keys(used).map(Number).sort((a, b) => a - b);
        let options = `<option value="">${@js(\App\Support\FrontendLocale::text('— छान्नुहोस् —'))}</option>`;
        for (let n = 1; n <= MAX; n++) options += `<option value="${n}">${np(n)}</option>`;
        select.innerHTML = options;
        select.value = String(wanted || Math.min(MAX, (nums.length ? nums[nums.length - 1] : 0) + 1));

        document.getElementById('childOrderRelation').textContent = RELATION[g] || @js(\App\Support\FrontendLocale::text('सन्तान'));
        document.getElementById('childOrderTaken').textContent = nums.length
            ? @js(\App\Support\FrontendLocale::text('हालका: ')) + nums.map(n => `${np(n)} ${used[n]}`).join(', ')
            : @js(\App\Support\FrontendLocale::text('अहिलेसम्म कोही दर्ता छैन।'));
    }

    gender.addEventListener('change', fill);
    select.addEventListener('change', () => { wanted = parseInt(select.value, 10) || null; });
    fill();
})();

document.querySelectorAll('.js-photo-limit').forEach(input => {
    input.addEventListener('change', function () {
        const old = this.parentElement.querySelector('.photo-limit-error');
        if (old) old.remove();

        if (this.files && this.files[0] && this.files[0].size > 500 * 1024) {
            const msg = document.createElement('p');
            msg.className = 'photo-limit-error text-[10px] text-red-500 mt-1 font-medium';
            msg.textContent = @js(\App\Support\FrontendLocale::text('फोटो 500 KB भन्दा ठूलो छ। सानो फोटो छान्नुहोस्।'));
            this.insertAdjacentElement('afterend', msg);
            this.value = '';
        }
    });
});
</script>

@if($canInlineEdit)
<style>
    [data-edit] { cursor: pointer; border-radius: 6px; padding: 1px 4px; margin: -1px -4px; transition: background .15s, box-shadow .15s; }
    [data-edit]:hover { background: #eef2ff; box-shadow: inset 0 0 0 1px #c7d2fe; }
    [data-edit]:hover::after { content: ' ✎'; color: #6366f1; font-size: 11px; }
    [data-edit].inline-saved { background: #dcfce7; box-shadow: inset 0 0 0 1px #86efac; }
    .inline-editor { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 4px; margin-top: 2px; }
    .inline-editor .inputCompact, .inline-editor .textareaCompact { flex: 1 1 140px; min-width: 0; padding: 5px 8px; }
    .inline-editor button { flex: none; height: 30px; min-width: 30px; border-radius: 8px; font-size: 13px; font-weight: 700; }
    .inline-editor .ie-save { background: #16a34a; color: #fff; }
    .inline-editor .ie-save:hover { background: #15803d; }
    .inline-editor .ie-cancel { background: #f1f5f9; color: #475569; }
    .inline-editor .ie-cancel:hover { background: #e2e8f0; }
    .inline-editor .ie-err { flex-basis: 100%; font-size: 11px; color: #dc2626; font-weight: 600; }
    #inlineToast { position: fixed; left: 50%; bottom: 24px; transform: translateX(-50%) translateY(20px); opacity: 0; z-index: 90;
        background: #0f172a; color: #fff; padding: 8px 16px; border-radius: 999px; font-size: 13px; font-weight: 600;
        box-shadow: 0 10px 30px rgba(15,23,42,.25); transition: opacity .2s, transform .2s; pointer-events: none; }
    #inlineToast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
    #inlineToast.err { background: #dc2626; }
</style>
<div id="inlineToast" role="status" aria-live="polite"></div>
<script>
// Admin inline edit: click a value → edit → Enter / ✓ saves that one field right away
(() => {
    const url = @json(\App\Support\FrontendLocale::route('admin.persons.inline', $person));
    const csrf = @json(csrf_token());
    const MEMBER_TYPES = @js(\App\Support\MemberType::all());
    const GENDERS = { male: @js(\App\Support\FrontendLocale::text('Male')), female: @js(\App\Support\FrontendLocale::text('Female')), other: @js(\App\Support\FrontendLocale::text('Other')), unknown: @js(\App\Support\FrontendLocale::text('Unknown')) };
    const toast = document.getElementById('inlineToast');
    let toastTimer, openEditor = null;

    function notify(text, isErr = false) {
        toast.textContent = text;
        toast.classList.toggle('err', isErr);
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2200);
    }

    function makeInput(type, value) {
        let el;
        if (type === 'member_type') {
            el = document.createElement('select');
            el.className = 'inputCompact bg-white';
            el.innerHTML = '<option value="">—</option>' + MEMBER_TYPES.map(t => `<option value="${t}">${t}</option>`).join('');
            el.value = value || '';
        } else if (type === 'gender') {
            el = document.createElement('select');
            el.className = 'inputCompact bg-white';
            el.innerHTML = Object.entries(GENDERS).map(([k, v]) => `<option value="${k}">${v}</option>`).join('');
            el.value = value || 'unknown';
        } else if (type === 'textarea') {
            el = document.createElement('textarea');
            el.className = 'textareaCompact';
            el.rows = 5;
            el.value = value;
        } else {
            el = document.createElement('input');
            el.type = type === 'date' ? 'date' : (type === 'email' ? 'email' : 'text');
            el.className = 'inputCompact';
            el.value = value;
        }
        return el;
    }

    function edit(span) {
        if (openEditor) openEditor.cancel();
        const field = span.dataset.edit;
        const type = span.dataset.type;
        const wrap = document.createElement('div');
        wrap.className = 'inline-editor';
        const input = makeInput(type, span.dataset.value || '');
        const save = Object.assign(document.createElement('button'), { type: 'button', className: 'ie-save', textContent: '✓', title: @js(\App\Support\FrontendLocale::text('सेभ (Enter)')) });
        const cancel = Object.assign(document.createElement('button'), { type: 'button', className: 'ie-cancel', textContent: '✕', title: @js(\App\Support\FrontendLocale::text('रद्द (Esc)')) });
        const err = Object.assign(document.createElement('div'), { className: 'ie-err' });
        wrap.append(input, save, cancel, err);

        span.hidden = true;
        span.insertAdjacentElement('afterend', wrap);
        input.focus();
        if (input.select && type !== 'gender') input.select();

        const close = () => { wrap.remove(); span.hidden = false; openEditor = null; };
        openEditor = { cancel: close };

        async function submit() {
            save.disabled = true; save.textContent = '…'; err.textContent = '';
            try {
                const res = await fetch(url, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ field, value: input.value }),
                });
                const data = await res.json().catch(() => ({ message: @js(\App\Support\FrontendLocale::text('सेभ गर्न सकिएन (session सकिएको हुन सक्छ — पेज refresh गर्नुहोस्)।')) }));
                if (!res.ok) throw new Error(data.message || @js(\App\Support\FrontendLocale::text('सेभ गर्न सकिएन।')));

                span.dataset.value = data.value ?? '';
                span.textContent = field === 'bio' && data.display === '-' ? 'No biography available.' : data.display;
                document.querySelectorAll(`[data-sync="${field}"]`).forEach(el => {
                    el.textContent = data.display === '-' && field === 'display_name_np' ? @js(\App\Support\FrontendLocale::text('नेपाली नाम छैन')) : data.display;
                });
                document.querySelectorAll(`[data-edit="${field}"]`).forEach(el => {
                    if (el === span) return;
                    el.dataset.value = data.value ?? '';
                    el.textContent = data.display;
                });
                close();
                span.classList.add('inline-saved');
                setTimeout(() => span.classList.remove('inline-saved'), 1200);
                notify(data.message || @js(\App\Support\FrontendLocale::text('सेभ भयो ✓')));
            } catch (e) {
                err.textContent = e.message;
                save.disabled = false; save.textContent = '✓';
                notify(e.message, true);
            }
        }

        save.addEventListener('click', submit);
        cancel.addEventListener('click', close);
        input.addEventListener('keydown', e => {
            if (e.key === 'Escape') { e.preventDefault(); close(); }
            // Enter saves; in the bio box use Ctrl/⌘+Enter so Enter still makes a new line
            if (e.key === 'Enter' && (type !== 'textarea' || e.ctrlKey || e.metaKey)) { e.preventDefault(); submit(); }
        });
    }

    document.querySelectorAll('[data-edit]').forEach(span => {
        span.title = @js(\App\Support\FrontendLocale::text('क्लिक गरेर सम्पादन गर्नुहोस्'));
        span.tabIndex = 0;
        span.addEventListener('click', () => edit(span));
        span.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); edit(span); } });
    });
})();
</script>
@endif

@include('partials.photo-camera')
@endsection
