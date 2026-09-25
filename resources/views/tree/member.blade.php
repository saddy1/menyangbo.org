@extends('layouts.app')
@section('title', $person->display_name . ' • प्रोफाइल')

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
        <span><strong>Admin Direct Edit Mode</strong> — तपाईं Admin हुनुहुन्छ। सबै परिवर्तनहरू सिधै लागू हुनेछन्, अनुरोध जाँदैन।</span>
        <a href="{{ route('admin.persons.edit', $person) }}"
           class="ml-auto shrink-0 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700 transition-colors">
            Admin Edit →
        </a>
    </div>
    @endif
    @endauth

    {{-- Auth notice --}}
    @guest
    <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-blue-800 text-sm flex items-center justify-between gap-3 flex-wrap">
        <span>अनुरोध पठाउन (Add Child, Edit, Deceased, Add Wife) <strong>लगइन</strong> आवश्यक छ।</span>
        <div class="flex gap-2">
            <a href="{{ route('login', ['next' => request()->fullUrl()]) }}" class="bg-blue-600 text-white px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-700">लगइन</a>
            <a href="{{ route('register') }}" class="border border-blue-600 text-blue-600 px-3 py-1 rounded-lg text-xs font-semibold hover:bg-blue-50">दर्ता</a>
        </div>
    </div>
    @endguest

    {{-- Errors summary --}}
    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <div class="font-bold">कृपया तलका त्रुटिहरू सुधार्नुहोस्:</div>
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
            <h1 class="text-2xl font-bold text-slate-900 truncate">{{ $person->display_name }}</h1>

            <div class="text-sm text-slate-600 mt-2 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span>ID: <span class="font-semibold text-slate-900">{{ $person->id }}</span></span>
                <span class="text-slate-300">•</span>
                <span>Pusta: <span class="font-semibold text-slate-900">{{ $person->pusta ?? '—' }}</span></span>
                <span class="text-slate-300">•</span>
                <span>Gender: <span class="font-semibold text-slate-900">{{ ucfirst($person->gender ?? 'unknown') }}</span></span>

                @if ($person->is_deceased)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-50 text-red-700 text-xs font-semibold border border-red-100">
                        Deceased
                    </span>
                @endif
            </div>
        </div>

        <div class="flex gap-2 shrink-0">
            <a href="{{ route('tree.index', ['root_id' => $person->id]) }}"
               class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium hover:bg-slate-50 transition-colors">
                Open in Tree
            </a>
            <a href="{{ url()->previous() }}"
               class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition-colors">
                Back
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
                        मृत्यु विवरण सहित
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
                    <div>
                        <div class="text-2xl font-bold text-slate-900">{{ $person->display_name }}</div>
                        <div class="mt-1 text-sm text-slate-500">
                            {{ $person->display_name_np ?: 'नेपाली नाम छैन' }}
                            @if($person->display_name_limbu)
                                <span class="mx-2 text-slate-300">•</span>{{ $person->display_name_limbu }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-slate-900">
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">सदस्य न.</span> <span class="font-medium">{{ $person->member_no ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">सदस्यको प्रकार</span> <span class="font-medium">{{ $person->member_type ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">नेपाली नाम</span> <span class="font-medium">{{ $person->display_name ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">Full Name (English)</span> <span class="font-medium">{{ $person->display_name_np ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">Limbu Name / ᤕᤰᤌᤢᤱ नाम</span> <span class="font-medium">{{ $person->display_name_limbu ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">सदस्यता</span> <span class="font-medium">{{ $person->membership ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">जन्म मिति (A.D.)</span>
                    <span class="font-medium">{{ $person->birth_date ? $person->birth_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">जन्म मिति (B.S.)</span>
                    <span class="font-medium">{{ $person->birth_date_bs ?? '-' }}</span>
                </div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">जन्मस्थान</span> <span class="font-medium">{{ $person->birth_place ?? '-' }}</span></div>

                {{-- Father / Mother --}}
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">बुबाको नाम</span>
                    @if (!empty($father))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ route('member.page', $father->id) }}">
                            {{ $father->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>

                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">आमाको नाम</span>
                    @if (!empty($mother))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ route('member.page', $mother->id) }}">
                            {{ $mother->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>

                {{-- Grandparents (only if data exists) --}}
                @if(!empty($grandfather) || !empty($grandmother))
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">हजुरबुबाको नाम</span>
                    @if(!empty($grandfather))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ route('member.page', $grandfather->id) }}">
                            {{ $grandfather->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>
                <div class="border-b border-slate-100 py-2 pr-3">
                    <span class="text-slate-500 block text-xs mb-0.5">हजुरआमाको नाम</span>
                    @if(!empty($grandmother))
                        <a class="font-medium text-blue-600 hover:underline" href="{{ route('member.page', $grandmother->id) }}">
                            {{ $grandmother->display_name }}
                        </a>
                    @else
                        <span class="font-medium text-slate-400">-</span>
                    @endif
                </div>
                @endif

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">घरको ठेगाना</span> <span class="font-medium">{{ $person->address ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">मोबाइल नम्बर</span> <span class="font-medium">{{ $person->mobile ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">Email ID</span> <span class="font-medium break-all">{{ $person->email ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">शिक्षा</span> <span class="font-medium">{{ $person->education ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">पेशा</span> <span class="font-medium">{{ $person->occupation ?? '-' }}</span></div>
                <div class="border-b border-slate-100 py-2 pr-3"><span class="text-slate-500 block text-xs mb-0.5">Registered By</span> <span class="font-medium">{{ $person->registered_by ?? '-' }}</span></div>

                <div class="border-b border-slate-100 py-2 pr-3 lg:col-span-4"><span class="text-slate-500 block text-xs mb-0.5">Updated Date</span>
                    <span class="font-medium">{{ $person->updated_at?->format('Y-m-d') ?? '-' }}</span>
                </div>
            </div>

            {{-- Death details --}}
            @if ($person->is_deceased)
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50/50 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="font-bold text-slate-900">मृत्यु विवरण</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm text-slate-900">
                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">मृत्यु मिति (A.D.)</span>
                            <span class="font-medium">{{ $person->death_date ? $person->death_date->format('Y-m-d') : '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">मृत्यु स्थान</span>
                            <span class="font-medium">{{ $person->death_place ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-slate-500 block text-xs mb-0.5">मृत्यु तिथी</span>
                            <span class="font-medium">{{ $person->death_tithi ?? '-' }}</span>
                        </div>

                        <div class="lg:col-span-2">
                            <span class="text-slate-500 block text-xs mb-0.5">मृत्युको कारण</span>
                            <span class="font-medium">{{ $person->death_reason ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Admin photo upload --}}
            @auth
            @if(Auth::user()->isAdmin())
            <div class="mt-5 pt-4 border-t border-dashed border-indigo-200">
                <form method="POST" action="{{ route('person.upload-photo', $person) }}" enctype="multipart/form-data"
                      class="flex items-center gap-3 flex-wrap">
                    @csrf
                    <span class="text-xs font-semibold text-indigo-700 shrink-0">📸 फोटो अपलोड (Admin)</span>
                    <div class="flex-1 min-w-0">
                        <input type="file" name="photo" id="memberPhotoInput" data-camera data-camera-autosubmit
                               accept="image/jpeg,image/jpg,image/png,image/webp" class="text-xs w-full">
                        <p id="memberPhotoErr" class="text-[10px] text-red-500 mt-0.5" style="display:none">⚠ 500 KB भन्दा बढी छ</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Max 500 KB · JPEG / PNG / WebP</p>
                    </div>
                    <button type="submit"
                            class="px-3 py-1.5 bg-indigo-600 text-white text-xs rounded-lg font-semibold hover:bg-indigo-700 shrink-0">
                        अपलोड
                    </button>
                </form>
                <script>
                document.getElementById('memberPhotoInput')?.addEventListener('change', function () {
                    const err = document.getElementById('memberPhotoErr');
                    if (this.files && this.files[0] && this.files[0].size > 500 * 1024) {
                        err.style.display = 'block';
                        this.value = '';
                    } else if (err) {
                        err.style.display = 'none';
                    }
                });
                </script>
            </div>
            @endif
            @endauth
        </div>

        {{-- Relations --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
            <div class="text-lg font-bold mb-4 text-slate-900">Relations</div>

            <div class="text-sm space-y-5 text-slate-900">

                {{-- Parents --}}
                <div>
                    <div class="text-slate-500 text-xs font-semibold mb-2 uppercase tracking-wider">Parents</div>
                    <div class="space-y-2">
                        @forelse($person->parents as $pp)
                            <a class="group block px-4 py-2.5 rounded-xl border border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white transition-all"
                               href="{{ route('member.page', $pp->id) }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium group-hover:text-blue-600 truncate">{{ $pp->display_name }}</span>
                                    <span class="text-xs text-slate-400">{{ $pp->member_no ?? '#'.$pp->id }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-slate-400 italic text-xs">No records found</div>
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
                               href="{{ route('member.page', $s->id) }}">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium group-hover:text-blue-600 truncate">{{ $s->display_name }}</span>
                                    <span class="text-xs text-slate-400">{{ $s->member_no ?? '#'.$s->id }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="text-slate-400 italic text-xs">No records found</div>
                        @endforelse
                    </div>
                </div>

                {{-- Children: sons and daughters in separate columns --}}
                @php
                    $allChildren = $children ?? $person->children;
                    $childColumns = [
                        ['title' => 'छोरा', 'head' => 'bg-blue-50 text-blue-700 border-blue-100', 'num' => 'bg-blue-600', 'items' => $allChildren->where('gender', 'male')],
                        ['title' => 'छोरी', 'head' => 'bg-pink-50 text-pink-700 border-pink-100', 'num' => 'bg-pink-500', 'items' => $allChildren->where('gender', 'female')],
                    ];
                    $otherChildren = $allChildren->whereNotIn('gender', ['male', 'female']);
                    if ($otherChildren->isNotEmpty()) {
                        $childColumns[] = ['title' => 'सन्तान', 'head' => 'bg-slate-50 text-slate-600 border-slate-100', 'num' => 'bg-violet-600', 'items' => $otherChildren];
                    }
                @endphp
                <div>
                    <div class="text-slate-500 text-xs font-semibold mb-2 uppercase tracking-wider">
                        Children ({{ $allChildren->count() }})
                    </div>
                    @if($allChildren->isEmpty())
                        <div class="text-slate-400 italic text-xs">No records found</div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($childColumns as $col)
                                <div class="min-w-0">
                                    <div class="mb-2 inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-bold {{ $col['head'] }}">
                                        {{ $col['title'] }} ({{ \App\Support\BirthOrder::npDigits($col['items']->count()) }})
                                    </div>
                                    <div class="space-y-2">
                                        @forelse($col['items'] as $cc)
                                            <a class="group flex items-center gap-3 px-3 py-2 rounded-xl border border-slate-100 bg-slate-50 hover:border-slate-300 hover:bg-white transition-all"
                                               href="{{ route('member.page', $cc->id) }}">
                                                <span class="flex-none w-6 h-6 rounded-full {{ $col['num'] }} text-white text-[11px] font-bold flex items-center justify-center">
                                                    {{ $cc->birth ? \App\Support\BirthOrder::npDigits($cc->birth['rank']) : '•' }}
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block font-medium group-hover:text-blue-600 truncate">{{ $cc->display_name }}</span>
                                                    <span class="block text-[11px] text-slate-400 truncate">
                                                        {{ collect([$cc->birth['word'] ?? null, $cc->member_no ?? '#'.$cc->id])->filter()->implode(' · ') }}
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
        <div class="text-lg font-bold mb-3 text-slate-900">जीवनी</div>
        <div class="text-sm text-slate-700 whitespace-pre-line leading-relaxed">
            {{ $person->bio ?: 'No biography available.' }}
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-wrap gap-3 mt-5">
        @if (!$person->is_deceased)
            @auth
            <button onclick="document.getElementById('deathModal').style.display='flex'"
                    class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
                Claim Dead
            </button>
            @else
            <a href="{{ route('login', ['next' => request()->fullUrl()]) }}"
               class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
                Login to Claim Dead
            </a>
            @endauth
        @endif

        @auth
        <button onclick="document.getElementById('childModal').style.display='flex'"
                class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm hover:bg-slate-800 font-semibold shadow-sm transition-colors">
            Add Child
        </button>
        @else
        <a href="{{ route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-slate-900 text-white rounded-xl text-sm hover:bg-slate-800 font-semibold shadow-sm transition-colors">
            Login to Add Child
        </a>
        @endauth

        @auth
        <button onclick="document.getElementById('editModal').style.display='flex'"
                class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
            Edit Profile
        </button>
        @else
        <a href="{{ route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-white border border-slate-300 shadow-sm rounded-xl text-sm hover:bg-slate-50 text-slate-900 font-semibold transition-colors">
            Login to Edit Profile
        </a>
        @endauth

        @auth
        <button onclick="document.getElementById('marriageModal').style.display='flex'"
                class="px-4 py-2 bg-white border border-blue-300 shadow-sm rounded-xl text-sm hover:bg-blue-50 text-blue-800 font-semibold transition-colors">
            Add Marriage
        </button>
        @else
        <a href="{{ route('login', ['next' => request()->fullUrl()]) }}"
           class="px-4 py-2 bg-white border border-blue-300 shadow-sm rounded-xl text-sm hover:bg-blue-50 text-blue-800 font-semibold transition-colors">
            Login to Add Marriage
        </a>
        @endauth
    </div>

    {{-- ========================= DEATH MODAL ========================= --}}
    <div id="deathModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">
        
        {{-- Modal Container --}}
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl flex flex-col max-h-[90vh] modal-panel">
            
            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <h2 class="text-lg font-bold text-slate-900">मृत्यु सुतक जानकारी</h2>
                <button type="button" 
                        onclick="document.getElementById('deathModal').style.display='none'" 
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form method="POST" action="{{ route('request.death', $person->id) }}" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="form_type" value="death">

                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-slate-900">
                        <div>
                            <label class="block font-medium text-slate-700 mb-1">मृत्यु मिति (A.D.)</label>
                            <input type="date" name="death_date_ad" value="{{ old('death_date_ad') }}" class="inputCompact">
                            @error('death_date_ad') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">मृत्यु मिति (B.S.)</label>
                            <input type="text" name="death_date_bs" value="{{ old('death_date_bs') }}" placeholder="2081-01-15" class="inputCompact">
                            @error('death_date_bs') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">मृत्यु स्थान</label>
                            <input type="text" name="death_place" value="{{ old('death_place') }}" class="inputCompact">
                            @error('death_place') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">मृत्यु तिथि</label>
                            <input type="text" name="death_tithi" value="{{ old('death_tithi') }}" class="inputCompact">
                            @error('death_tithi') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-medium text-slate-700 mb-1">मृत्युको कारण</label>
                            <textarea name="death_reason" class="textareaCompact">{{ old('death_reason') }}</textarea>
                            @error('death_reason') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        @guest
                        <div class="sm:col-span-2 pt-2">
                            <label class="block font-medium text-slate-700 mb-1">तपाईंको नाम <span class="text-red-500">*</span></label>
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
                            सिधै सेभ गर्नुहोस्
                        @else
                            अनुरोध पठाउनुहोस्
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================= CHILD MODAL ========================= --}}
    <div id="childModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">

        {{-- Modal Container --}}
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl flex flex-col max-h-[86vh] modal-panel">

            {{-- Header (Fixed) --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0 bg-white rounded-t-2xl">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">नयाँ सन्तान जानकारी</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Fill in the details for the new child record.</p>
                </div>
                <button type="button"
                        onclick="document.getElementById('childModal').style.display='none'"
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Form Body (Scrollable) --}}
            <form method="POST" action="{{ route('request.child', $person->id) }}" enctype="multipart/form-data" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="form_type" value="child">

                <div class="p-4 overflow-y-auto bg-slate-50/60">
                    <div class="space-y-4 text-sm text-slate-900">

                        {{-- BASIC INFO --}}
                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Basic Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">नेपाली नाम <span class="text-red-500">*</span></label>
                                    <input name="display_name" value="{{ old('display_name') }}" placeholder="John Doe" class="inputCompact">
                                    @error('display_name') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Full Name (English) </label>
                                    <input name="display_name_np" value="{{ old('display_name_np') }}" placeholder="In English" class="inputCompact">
                                    @error('display_name_np') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Limbu Name / ᤕᤰᤌᤢᤱ नाम</label>
                                    <input name="display_name_limbu" value="{{ old('display_name_limbu') }}" placeholder="Limbu script name" class="inputCompact">
                                    @error('display_name_limbu') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Gender</label>
                                    <select name="gender" id="childGenderSelect" class="inputCompact max-w-xs bg-white">
                                        @php $g = old('gender','unknown'); @endphp
                                        <option value="unknown" @selected($g==='unknown')>Unknown</option>
                                        <option value="male" @selected($g==='male')>Male</option>
                                        <option value="female" @selected($g==='female')>Female</option>
                                        <option value="other" @selected($g==='other')>Other</option>
                                    </select>
                                    @error('gender') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">
                                        सन्तान क्रम (<span id="childOrderRelation">सन्तान</span>)
                                    </label>
                                    <select name="birth_order" id="childBirthOrder" class="inputCompact max-w-xs bg-white"
                                            data-old="{{ old('birth_order') }}"></select>
                                    <p class="text-[10px] text-slate-400 mt-1" id="childOrderTaken"></p>
                                    @error('birth_order') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Profile Photo</label>
                                    <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp" class="inputCompact js-photo-limit" data-camera>
                                    <p class="text-[10px] text-slate-400 mt-1">Max 500 KB</p>
                                    @error('photo') <div class="err">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- BIRTH & CONTACT --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white border border-slate-100 rounded-xl p-4">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Birth Details</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Date (A.D.)</label>
                                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="inputCompact">
                                        @error('birth_date') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Date (B.S.)</label>
                                        <input type="text" name="birth_date_bs" value="{{ old('birth_date_bs') }}" placeholder="2081-01-15" class="inputCompact">
                                        @error('birth_date_bs') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Place</label>
                                        <input name="birth_place" value="{{ old('birth_place') }}" placeholder="जन्मस्थान" class="inputCompact">
                                        @error('birth_place') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border border-slate-100 rounded-xl p-4">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Contact Info</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Mobile Number</label>
                                        <input name="mobile" value="{{ old('mobile') }}" placeholder="मोबाइल नम्बर" class="inputCompact">
                                        @error('mobile') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Email Address</label>
                                        <input name="email" value="{{ old('email') }}" placeholder="Email ID" class="inputCompact">
                                        @error('email') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- PERSONAL --}}
                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Personal Attributes</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Education</label>
                                    <input name="education" value="{{ old('education') }}" placeholder="शिक्षा" class="inputCompact">
                                    @error('education') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Occupation</label>
                                    <input name="occupation" value="{{ old('occupation') }}" placeholder="पेशा" class="inputCompact">
                                    @error('occupation') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Lineage</label>
                                    <input name="lineage" value="{{ old('lineage') }}" placeholder="Lineage" class="inputCompact">
                                    @error('lineage') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Family Type</label>
                                    <input name="family_type" value="{{ old('family_type') }}" placeholder="परिवारको प्रकार" class="inputCompact">
                                    @error('family_type') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Blood Group</label>
                                    <input name="blood_group" value="{{ old('blood_group') }}" placeholder="रक्त समूह" class="inputCompact">
                                    @error('blood_group') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Rashifal</label>
                                    <input name="rashifal" value="{{ old('rashifal') }}" placeholder="राशिफल" class="inputCompact">
                                    @error('rashifal') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Religion</label>
                                    <input name="religion" value="{{ old('religion') }}" placeholder="धर्म" class="inputCompact">
                                    @error('religion') <div class="err">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- NOTES & BIO --}}
                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Additional Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Special Note</label>
                                    <textarea name="special_note" placeholder="विशेष नोट" class="textareaCompact">{{ old('special_note') }}</textarea>
                                    @error('special_note') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Biography</label>
                                    <textarea name="bio" placeholder="जीवनी" class="textareaCompact h-24">{{ old('bio') }}</textarea>
                                    @error('bio') <div class="err">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- SUBMITTER --}}
                        @guest
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                            <label class="block text-xs font-medium text-slate-700 mb-1">तपाईंको नाम (Submitter) <span class="text-red-500">*</span></label>
                            <input name="submitted_name" value="{{ old('submitted_name') }}" placeholder="तपाईंको नाम *" class="inputCompact max-w-sm bg-white">
                            @error('submitted_name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        @endguest

                    </div>
                </div>

                {{-- Footer (Fixed) --}}
                <div class="px-5 py-3 border-t border-slate-100 bg-white shrink-0 rounded-b-2xl flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('childModal').style.display='none'"
                            class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-xl font-semibold shadow-sm transition-colors">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            सिधै थप्नुहोस्
                        @else
                            सन्तान अनुरोध पठाउनुहोस्
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

    {{-- ========================= EDIT PROFILE MODAL ========================= --}}
    <div id="editModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl flex flex-col max-h-[86vh] modal-panel">

            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 shrink-0 bg-white rounded-t-2xl">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">प्रोफाइल सम्पादन अनुरोध</h2>
                    <p class="text-xs text-slate-500 mt-0.5">परिवर्तन गर्न चाहेका फिल्डहरू मात्र भर्नुहोस्।</p>
                </div>
                <button type="button"
                        onclick="document.getElementById('editModal').style.display='none'"
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('request.edit', $person->id) }}" enctype="multipart/form-data" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="form_type" value="edit">

                <div class="p-4 overflow-y-auto bg-slate-50/60">
                    <div class="space-y-4 text-sm text-slate-900">

                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Basic Information</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div class="lg:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">नेपाली नाम</label>
                                    <input name="display_name" value="{{ old('display_name', $person->display_name) }}" class="inputCompact">
                                    @error('display_name') <div class="err">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Full Name (English)</label>
                                    <input name="display_name_np" value="{{ old('display_name_np', $person->display_name_np) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Limbu Name / ᤕᤰᤌᤢᤱ नाम</label>
                                    <input name="display_name_limbu" value="{{ old('display_name_limbu', $person->display_name_limbu) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Profile Photo</label>
                                    <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/webp" class="inputCompact js-photo-limit" data-camera>
                                    <p class="text-[10px] text-slate-400 mt-1">Max 500 KB. Normal users send this as a request.</p>
                                    @error('photo') <div class="err">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-white border border-slate-100 rounded-xl p-4">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Birth & Address</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Date (A.D.)</label>
                                        <input type="date" name="birth_date" value="{{ old('birth_date', $person->birth_date?->format('Y-m-d')) }}" class="inputCompact">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Date (B.S.)</label>
                                        <input type="text" name="birth_date_bs" value="{{ old('birth_date_bs', $person->birth_date_bs) }}" placeholder="2081-01-15" class="inputCompact">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Birth Place</label>
                                        <input name="birth_place" value="{{ old('birth_place', $person->birth_place) }}" class="inputCompact">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">ठेगाना</label>
                                        <input name="address" value="{{ old('address', $person->address) }}" class="inputCompact">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border border-slate-100 rounded-xl p-4">
                                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Contact</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Mobile</label>
                                        <input name="mobile" value="{{ old('mobile', $person->mobile) }}" class="inputCompact">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                                        <input name="email" value="{{ old('email', $person->email) }}" class="inputCompact">
                                        @error('email') <div class="err">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Personal Attributes</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Education</label>
                                    <input name="education" value="{{ old('education', $person->education) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Occupation</label>
                                    <input name="occupation" value="{{ old('occupation', $person->occupation) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Blood Group</label>
                                    <input name="blood_group" value="{{ old('blood_group', $person->blood_group) }}" class="inputCompact">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Rashifal</label>
                                    <input name="rashifal" value="{{ old('rashifal', $person->rashifal) }}" class="inputCompact">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Religion</label>
                                    <input name="religion" value="{{ old('religion', $person->religion) }}" class="inputCompact">
                                </div>
                            </div>
                        </div>

                        <div class="bg-white border border-slate-100 rounded-xl p-4">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Additional Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Special Note</label>
                                    <textarea name="special_note" class="textareaCompact">{{ old('special_note', $person->special_note) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">Biography</label>
                                    <textarea name="bio" class="textareaCompact h-24">{{ old('bio', $person->bio) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-600 mb-1">परिवर्तनको कारण / नोट</label>
                                    <textarea name="submitted_note" placeholder="किन परिवर्तन चाहानुभयो?" class="textareaCompact">{{ old('submitted_note') }}</textarea>
                                </div>
                            </div>
                        </div>

                        @guest
                        <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                            <label class="block text-xs font-medium text-slate-700 mb-1">तपाईंको नाम (Submitter) <span class="text-red-500">*</span></label>
                            <input name="submitted_name" value="{{ old('submitted_name') }}" placeholder="तपाईंको नाम *" class="inputCompact max-w-sm bg-white">
                            @error('submitted_name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        @endguest

                    </div>
                </div>

                <div class="px-5 py-3 border-t border-slate-100 bg-white shrink-0 rounded-b-2xl flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('editModal').style.display='none'"
                            class="px-5 py-2.5 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-xl font-semibold shadow-sm transition-colors">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            सिधै सेभ गर्नुहोस्
                        @else
                            अनुरोध पठाउनुहोस्
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================= MARRIAGE MODAL ========================= --}}
    <div id="marriageModal" style="display:none" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-slate-900/50 backdrop-blur-sm modal-backdrop">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl flex flex-col max-h-[90vh] modal-panel">

            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
                <h2 class="text-lg font-bold text-slate-900">विवाह जानकारी अनुरोध</h2>
                <button type="button"
                        onclick="document.getElementById('marriageModal').style.display='none'"
                        class="p-2 -mr-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-full transition-colors focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('request.marriage', $person->id) }}" class="flex flex-col overflow-hidden">
                @csrf
                <input type="hidden" name="form_type" value="marriage">

                <div class="p-6 overflow-y-auto">
                    <div class="space-y-4 text-sm text-slate-900">

                        <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-xs text-blue-800">
                            पहिले ID वा नाम खोजेर प्रणालीमा भएको व्यक्ति छान्नुहोस्। नभए तल नयाँ जीवनसाथीको नाम लेख्नुहोस्।
                        </div>

                        <div class="relative">
                            <label class="block font-medium text-slate-700 mb-1">पहिले प्रणालीमा खोज्नुहोस् (ID / English / Nepali / Limbu)</label>
                            <input type="text" id="spouseExistingSearch" placeholder="e.g. 42, Sita, सीता..."
                                   class="inputCompact" autocomplete="off">
                            <input type="hidden" name="spouse_person_id" id="spousePersonId" value="{{ old('spouse_person_id') }}">
                            @error('spouse_person_id') <div class="err">{{ $message }}</div> @enderror
                            <div id="spouseSearchResults"
                                 class="hidden absolute left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-50 max-h-56 overflow-y-auto"></div>

                            <div id="selectedSpouseBox"
                                 class="hidden mt-2 rounded-xl border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold">छानिएको:</span>
                                    <span id="selectedSpouseName"></span>
                                    <button type="button" id="clearSelectedSpouse"
                                            class="ml-auto text-xs text-red-600 hover:underline">हटाउनुहोस्</button>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">नयाँ जीवनसाथीको नाम <span class="text-slate-400 text-xs">(माथि भेटिएन भने मात्र)</span></label>
                            <input type="text" name="spouse_name" value="{{ old('spouse_name') }}" placeholder="जीवनसाथीको पूरा नाम (नेपाली)" class="inputCompact">
                            @error('spouse_name') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Full Name (English)</label>
                                <input type="text" name="spouse_name_np" value="{{ old('spouse_name_np') }}" placeholder="English name" class="inputCompact">
                            </div>
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">Limbu Name / ᤕᤰᤌᤢᤱ नाम</label>
                                <input type="text" name="spouse_name_limbu" value="{{ old('spouse_name_limbu') }}" placeholder="Limbu script name" class="inputCompact">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">नयाँ जीवनसाथीको लिङ्ग <span class="text-slate-400 text-xs">(ID नभएमा)</span></label>
                            @php $sg = old('spouse_gender', $person->gender === 'male' ? 'female' : ($person->gender === 'female' ? 'male' : 'unknown')); @endphp
                            <select name="spouse_gender" class="inputCompact bg-white max-w-xs">
                                <option value="female" @selected($sg === 'female')>पत्नी / Female</option>
                                <option value="male" @selected($sg === 'male')>पति / Male</option>
                                <option value="other" @selected($sg === 'other')>Other</option>
                                <option value="unknown" @selected($sg === 'unknown')>Unknown</option>
                            </select>
                            @error('spouse_gender') <div class="err">{{ $message }}</div> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium text-slate-700 mb-1">सम्बन्धको प्रकार</label>
                                <select name="type" class="inputCompact bg-white">
                                    @php $t = old('type', 'married'); @endphp
                                    <option value="married"  @selected($t==='married')>Married</option>
                                    <option value="partner"  @selected($t==='partner')>Partner</option>
                                    <option value="divorced" @selected($t==='divorced')>Divorced</option>
                                    <option value="widowed"  @selected($t==='widowed')>Widowed</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-medium text-slate-700 mb-1">विवाह मिति (A.D.)</label>
                                <input type="date" name="start_date" value="{{ old('start_date') }}" class="inputCompact">
                            </div>
                        </div>

                        <div>
                            <label class="block font-medium text-slate-700 mb-1">थप नोट</label>
                            <textarea name="notes" class="textareaCompact">{{ old('notes') }}</textarea>
                        </div>

                        @guest
                        <div class="pt-2">
                            <label class="block font-medium text-slate-700 mb-1">तपाईंको नाम <span class="text-red-500">*</span></label>
                            <input type="text" name="submitted_name" value="{{ old('submitted_name') }}" class="inputCompact bg-slate-50">
                            @error('submitted_name') <div class="err">{{ $message }}</div> @enderror
                        </div>
                        @endguest
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0 rounded-b-2xl">
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl w-full font-semibold shadow-sm transition-colors">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            सिधै थप्नुहोस्
                        @else
                            अनुरोध पठाउनुहोस्
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

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
(() => {
    const searchInput = document.getElementById('spouseExistingSearch');
    const resultsBox = document.getElementById('spouseSearchResults');
    const hiddenId = document.getElementById('spousePersonId');
    const selectedBox = document.getElementById('selectedSpouseBox');
    const selectedName = document.getElementById('selectedSpouseName');
    const clearBtn = document.getElementById('clearSelectedSpouse');
    const searchUrl = @json(route('people.search'));
    let timer = null;

    if (!searchInput || !resultsBox || !hiddenId) return;

    function showSelected(id, label) {
        hiddenId.value = id;
        selectedName.textContent = `${label} (#${id})`;
        selectedBox.classList.remove('hidden');
        resultsBox.classList.add('hidden');
        searchInput.value = '';
    }

    function clearSelected() {
        hiddenId.value = '';
        selectedName.textContent = '';
        selectedBox.classList.add('hidden');
    }

    function personLabel(person) {
        const parts = [person.display_name];
        if (person.display_name_np) parts.push(person.display_name_np);
        if (person.display_name_limbu) parts.push(person.display_name_limbu);
        return parts.filter(Boolean).join(' / ');
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    clearBtn?.addEventListener('click', clearSelected);

    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        const term = searchInput.value.trim();
        if (!term) {
            resultsBox.classList.add('hidden');
            return;
        }

        timer = setTimeout(async () => {
            const res = await fetch(`${searchUrl}?term=${encodeURIComponent(term)}`);
            const rows = await res.json();

            if (!rows.length) {
                resultsBox.innerHTML = '<div class="px-4 py-3 text-xs text-slate-400">भेटिएन। तल नयाँ नाम लेख्नुहोस्।</div>';
                resultsBox.classList.remove('hidden');
                return;
            }

            resultsBox.innerHTML = rows.map(person => {
                const label = personLabel(person);
                const meta = [
                    `#${person.id}`,
                    person.gender || null,
                    person.pusta ? `पुस्ता ${person.pusta}` : null,
                ].filter(Boolean).join(' · ');

                const safeLabel = escapeHtml(label);
                const safeMeta = escapeHtml(meta);

                return `<button type="button"
                    class="w-full text-left px-4 py-2.5 border-b border-slate-100 last:border-0 hover:bg-blue-50"
                    data-id="${person.id}"
                    data-label="${safeLabel}">
                    <div class="font-semibold text-slate-800">${safeLabel}</div>
                    <div class="text-xs text-slate-400">${safeMeta}</div>
                </button>`;
            }).join('');
            resultsBox.classList.remove('hidden');

            resultsBox.querySelectorAll('button[data-id]').forEach(btn => {
                btn.addEventListener('click', () => showSelected(btn.dataset.id, btn.dataset.label));
            });
        }, 250);
    });

    document.addEventListener('click', event => {
        if (!searchInput.contains(event.target) && !resultsBox.contains(event.target)) {
            resultsBox.classList.add('hidden');
        }
    });

    if (hiddenId.value) {
        selectedName.textContent = `Person ID #${hiddenId.value}`;
        selectedBox.classList.remove('hidden');
    }
})();

// सन्तान क्रम for the add-child form: free places depend on the chosen gender
(() => {
    const gender = document.getElementById('childGenderSelect');
    const select = document.getElementById('childBirthOrder');
    if (!gender || !select) return;
    const taken = @json($takenOrders ?? []);
    const WORDS = {
        male:   ['जेठो', 'माहिलो', 'साहिलो', 'काहिलो', 'ठाहिलो'],
        female: ['जेठी', 'माहिली', 'साहिली', 'काहिली', 'ठाहिली'],
    };
    const RELATION = { male: 'छोरा', female: 'छोरी' };
    const np = n => String(n).replace(/[0-9]/g, d => '०१२३४५६७८९'[d]);
    let wanted = parseInt(select.dataset.old, 10) || null;

    function fill() {
        const g = gender.value;
        const used = taken[g] || {};
        const nums = Object.keys(used).map(Number);
        const max = Math.max(10, (nums.length ? Math.max(...nums) : 0) + 1);
        const free = [];
        for (let n = 1; n <= max; n++) if (!(n in used)) free.push(n);

        select.innerHTML = '<option value="">— छान्नुहोस् —</option>' + free.map(n => {
            const w = (WORDS[g] || [])[n - 1];
            return `<option value="${n}">${np(n)}${w ? ' — ' + w : ''}</option>`;
        }).join('');
        select.value = free.includes(wanted) ? String(wanted) : String(free[0] ?? '');

        document.getElementById('childOrderRelation').textContent = RELATION[g] || 'सन्तान';
        document.getElementById('childOrderTaken').textContent = nums.length
            ? 'पहिले नै: ' + nums.sort((a, b) => a - b).map(n => `${np(n)} ${used[n]}`).join(', ')
            : 'अहिलेसम्म कोही दर्ता छैन।';
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
            msg.textContent = 'फोटो 500 KB भन्दा ठूलो छ। सानो फोटो छान्नुहोस्।';
            this.insertAdjacentElement('afterend', msg);
            this.value = '';
        }
    });
});
</script>

@include('partials.photo-camera')
@endsection
