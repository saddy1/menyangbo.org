@extends('admin.layout')
@section('title', 'Committee Management')

@section('content')
<div class="py-6 max-w-6xl mx-auto" x-data="{ addCommittee: false }">
  <datalist id="committee_type_options">
    @foreach($types as $val => $label)
      <option value="{{ $val }}">{{ $label }}</option>
    @endforeach
  </datalist>

  {{-- Page header --}}
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
      <h1 class="text-xl font-bold text-gray-900">Committee Management</h1>
      <p class="text-sm text-gray-400 mt-0.5">Manage working and past committees with members.</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('committee.index') }}" target="_blank"
         class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200
                text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
        <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i> View Public
      </a>
      <button @click="addCommittee = true"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700
                     text-sm font-semibold text-white transition-colors">
        <i class="fa-solid fa-plus text-xs"></i> Add Committee
      </button>
    </div>
  </div>

  {{-- Committees compact list --}}
  <div class="space-y-3">
    @forelse($committees as $committee)

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-visible"
         x-data="{ open: false, addMember: false, editMemberId: null, editCommittee: false }">

      {{-- ── Committee row ── --}}
      <div class="px-4 py-3 flex items-center gap-3 group">

        {{-- Expand toggle --}}
        <button @click="open = !open"
                class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center
                       hover:bg-gray-50 transition shrink-0 text-gray-400">
          <i class="fa-solid text-xs transition-transform duration-200"
             :class="open ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
        </button>

        {{-- Badge: type --}}
        <span class="hidden sm:inline text-[10px] font-bold px-2 py-1 rounded-lg shrink-0
          {{ $committee->type === 'central'  ? 'bg-blue-50 text-blue-600'   :
             ($committee->type === 'district' ? 'bg-violet-50 text-violet-600' : 'bg-teal-50 text-teal-600') }}">
          {{ strtoupper(substr($committee->type_label, 0, 3)) }}
        </span>

        {{-- Name + meta --}}
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-gray-900 text-sm">{{ $committee->name }}</span>
            @if($committee->term_label)
              <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-100">
                {{ $committee->term_label }}
              </span>
            @endif
            <span class="text-xs px-2 py-0.5 rounded-full
              {{ $committee->is_active
                 ? 'bg-green-50 text-green-700 border border-green-100'
                 : 'bg-gray-100 text-gray-400 border border-gray-200' }}">
              {{ $committee->is_active ? 'Working' : 'Past' }}
            </span>
          </div>
          <p class="text-[11px] text-gray-400 mt-0.5">
            {{ $committee->type_label }} &bull; {{ $committee->members->count() }} member(s)
          </p>
        </div>

        {{-- Action buttons (always visible) --}}
        <div class="flex items-center gap-1 shrink-0">
          <button @click="editCommittee = true"
                  class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center
                         text-gray-400 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50
                         transition-colors text-xs">
            <i class="fa-solid fa-pen"></i>
          </button>
          <form method="POST" action="{{ route('admin.committees.destroy', $committee) }}"
                onsubmit="return confirm('Delete {{ addslashes($committee->name) }} and all its members?')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center
                           text-gray-400 hover:text-red-500 hover:border-red-300 hover:bg-red-50
                           transition-colors text-xs">
              <i class="fa-solid fa-trash"></i>
            </button>
          </form>
        </div>
      </div>

      {{-- ── Members compact grid ── --}}
      <div x-show="open" x-cloak class="border-t border-gray-100 px-4 pt-3 pb-4">

        @if($committee->members->isEmpty())
          <p class="text-xs text-gray-400 text-center py-2 mb-2">No members yet.</p>
        @else
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2 mb-3">
          @foreach($committee->members as $member)
          <div class="relative group/card flex flex-col items-center p-2.5 rounded-xl border border-gray-100
                      hover:border-blue-200 hover:bg-blue-50/30 transition-colors text-center">

            {{-- Photo --}}
            <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100 ring-2 ring-white shadow shrink-0">
              @if($member->photo_path)
                <img src="{{ asset($member->photo_path) }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
              @else
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200">
                  <i class="fa-solid fa-user text-blue-400 text-base"></i>
                </div>
              @endif
            </div>

            {{-- Name & position --}}
            <div class="mt-1.5 w-full">
              <div class="text-[11px] font-bold text-gray-800 truncate leading-tight">{{ $member->name }}</div>
              @if($member->position)
                <div class="text-[10px] text-blue-600 font-medium truncate mt-0.5">{{ $member->position }}</div>
              @endif
            </div>

            {{-- Hover actions --}}
            <div class="absolute top-1.5 right-1.5 hidden group-hover/card:flex gap-1">
              <button @click="editMemberId = {{ $member->id }}"
                      class="w-6 h-6 rounded-md bg-white border border-gray-200 shadow-sm
                             flex items-center justify-center text-gray-400 hover:text-blue-600
                             hover:border-blue-300 transition-colors">
                <i class="fa-solid fa-pen text-[9px]"></i>
              </button>
              <form method="POST"
                    action="{{ route('admin.committees.members.destroy', [$committee, $member]) }}"
                    onsubmit="return confirm('Remove {{ addslashes($member->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-6 h-6 rounded-md bg-white border border-gray-200 shadow-sm
                               flex items-center justify-center text-gray-400 hover:text-red-500
                               hover:border-red-300 transition-colors">
                  <i class="fa-solid fa-xmark text-[9px]"></i>
                </button>
              </form>
            </div>
          </div>
          @endforeach
        </div>
        @endif

        <button @click="addMember = true"
                class="w-full py-2 rounded-xl border border-dashed border-gray-300 text-xs font-semibold
                       text-gray-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50/30
                       transition-colors flex items-center justify-center gap-1.5">
          <i class="fa-solid fa-plus text-[10px]"></i> Add Member
        </button>
      </div>

      {{-- ════════════════════════════════
           EDIT COMMITTEE POPUP
      ════════════════════════════════ --}}
      <div x-show="editCommittee"
           x-cloak
           @keydown.escape.window="editCommittee = false"
           class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="editCommittee = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
              <h3 class="font-bold text-gray-900">Edit Committee</h3>
              <p class="text-xs text-gray-400 mt-0.5">{{ $committee->name }}</p>
            </div>
            <button @click="editCommittee = false"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center
                           text-gray-400 hover:text-gray-600 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form method="POST" action="{{ route('admin.committees.update', $committee) }}" class="p-6 space-y-4">
            @csrf @method('PUT')

            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1">Name *</label>
              <input type="text" name="name" value="{{ $committee->name }}" required
                     class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                            focus:ring-2 focus:ring-blue-500 outline-none transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Type *</label>
                <input type="text" name="type" value="{{ $committee->type }}" list="committee_type_options" required
                       placeholder="central, district, youth..."
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
                <p class="text-[10px] text-gray-400 mt-1">Choose existing or type a new committee type.</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Term / Year</label>
                <input type="text" name="term_label" value="{{ $committee->term_label }}"
                       placeholder="e.g. २०७८–२०८१"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ $committee->sort_order }}" min="0"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div class="flex items-center gap-2 pt-5">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                       id="act_{{ $committee->id }}" {{ $committee->is_active ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 w-4 h-4">
                <label for="act_{{ $committee->id }}" class="text-sm text-gray-700">Active</label>
              </div>
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit"
                      class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white
                             text-sm font-bold transition">
                <i class="fa-solid fa-check mr-1.5"></i>Save Changes
              </button>
              <button type="button" @click="editCommittee = false"
                      class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold
                             text-gray-600 hover:bg-gray-50 transition">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- ════════════════════════════════
           EDIT MEMBER POPUPS
      ════════════════════════════════ --}}
      @foreach($committee->members as $member)
      <div x-show="editMemberId === {{ $member->id }}"
           x-cloak
           @keydown.escape.window="editMemberId = null"
           class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"
             @click="editMemberId = null"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-100 ring-2 ring-gray-200 shrink-0">
                @if($member->photo_path)
                  <img src="{{ asset($member->photo_path) }}" alt="" class="w-full h-full object-cover">
                @else
                  <div class="w-full h-full flex items-center justify-center bg-blue-100">
                    <i class="fa-solid fa-user text-blue-400 text-sm"></i>
                  </div>
                @endif
              </div>
              <div>
                <h3 class="font-bold text-gray-900 text-sm">Edit Member</h3>
                <p class="text-xs text-gray-400">{{ $member->name }}</p>
              </div>
            </div>
            <button @click="editMemberId = null"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center
                           text-gray-400 hover:text-gray-600 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form method="POST"
                action="{{ route('admin.committees.members.update', [$committee, $member]) }}"
                enctype="multipart/form-data"
                class="p-6 space-y-4">
            @csrf @method('PUT')

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Name *</label>
                <input type="text" name="name" value="{{ $member->name }}" required
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Position</label>
                <input type="text" name="position" value="{{ $member->position }}"
                       placeholder="e.g. अध्यक्ष"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                <input type="email" name="email" value="{{ $member->email }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Contact</label>
                <input type="text" name="contact" value="{{ $member->contact }}"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ $member->sort_order }}" min="0"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Photo</label>
                @if($member->photo_path)
                  <img src="{{ asset($member->photo_path) }}"
                       class="w-10 h-10 rounded-full object-cover mb-1.5 ring-1 ring-gray-200">
                @endif
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2">
                <p class="text-[10px] text-gray-400 mt-0.5">Leave blank to keep current.</p>
              </div>
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit"
                      class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white
                             text-sm font-bold transition">
                <i class="fa-solid fa-check mr-1.5"></i>Save Changes
              </button>
              <button type="button" @click="editMemberId = null"
                      class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold
                             text-gray-600 hover:bg-gray-50 transition">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
      @endforeach

      {{-- ════════════════════════════════
           ADD MEMBER POPUP
      ════════════════════════════════ --}}
      <div x-show="addMember"
           x-cloak
           @keydown.escape.window="addMember = false"
           class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="addMember = false"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
              <h3 class="font-bold text-gray-900 text-sm">Add Member</h3>
              <p class="text-xs text-gray-400">{{ $committee->name }}</p>
            </div>
            <button @click="addMember = false"
                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center
                           text-gray-400 hover:text-gray-600 transition-colors">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form method="POST" action="{{ route('admin.committees.members.store', $committee) }}"
                enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Name *</label>
                <input type="text" name="name" required
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Position</label>
                <input type="text" name="position" placeholder="e.g. अध्यक्ष"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                <input type="email" name="email"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Contact</label>
                <input type="text" name="contact" placeholder="98XXXXXXXX"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="0" min="0"
                       class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                              focus:ring-2 focus:ring-blue-500 outline-none transition">
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Photo</label>
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-xs border border-gray-300 rounded-xl px-3 py-2">
              </div>
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit"
                      class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white
                             text-sm font-bold transition">
                <i class="fa-solid fa-plus mr-1.5"></i>Add Member
              </button>
              <button type="button" @click="addMember = false"
                      class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold
                             text-gray-600 hover:bg-gray-50 transition">
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>{{-- /committee card --}}
    @empty
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-10 text-center text-gray-400 text-sm">
      No committees yet. Click <strong>Add Committee</strong> to get started.
    </div>
    @endforelse
  </div>

  {{-- ════════════════════════════════
       ADD COMMITTEE POPUP (page-level)
  ════════════════════════════════ --}}
  <div x-show="addCommittee"
       x-cloak
       @keydown.escape.window="addCommittee = false"
       class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="addCommittee = false"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">Add New Committee</h3>
        <button @click="addCommittee = false"
                class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center
                       text-gray-400 hover:text-gray-600 transition-colors">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <form method="POST" action="{{ route('admin.committees.store') }}" class="p-6 space-y-4">
        @csrf

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1">Committee Name *</label>
          <input type="text" name="name" required
                 placeholder="e.g. केन्द्रीय कार्यसमिति"
                 class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                        focus:ring-2 focus:ring-blue-500 outline-none transition">
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Type *</label>
            <input type="text" name="type" list="committee_type_options" required
                   value="{{ old('type', 'central') }}"
                   placeholder="central, district, youth..."
                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                          focus:ring-2 focus:ring-blue-500 outline-none transition">
            <p class="text-[10px] text-gray-400 mt-1">Select suggestion or type new type.</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Term / Year</label>
            <input type="text" name="term_label" placeholder="e.g. २०७८–२०८१"
                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                          focus:ring-2 focus:ring-blue-500 outline-none transition">
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Sort Order</label>
            <input type="number" name="sort_order" value="0" min="0"
                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm
                          focus:ring-2 focus:ring-blue-500 outline-none transition">
          </div>
          <div class="flex items-center gap-2 pt-5">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="new_active" checked
                   class="rounded border-gray-300 text-blue-600 w-4 h-4">
            <label for="new_active" class="text-sm text-gray-700">Active</label>
          </div>
        </div>

        <div class="flex gap-2 pt-1">
          <button type="submit"
                  class="flex-1 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white
                         text-sm font-bold transition">
            <i class="fa-solid fa-plus mr-1.5"></i>Add Committee
          </button>
          <button type="button" @click="addCommittee = false"
                  class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold
                         text-gray-600 hover:bg-gray-50 transition">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
