@extends('admin.layout')
@section('title', 'Notice Management')

@section('content')
<div class="py-6 max-w-5xl mx-auto">

  {{-- Page title --}}
  <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
      <h1 class="text-xl font-bold text-gray-900">Notice Management</h1>
      <p class="text-sm text-gray-400 mt-0.5">
        Only the latest <strong>4 active notices</strong> scroll in the header bar.
        All active notices appear on the public page.
      </p>
    </div>
    <a href="{{ route('notices.public') }}" target="_blank"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200
              text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
      <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
      View Public Page
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

    {{-- ══════════════════════════════
         Add Notice  (2 cols)
    ══════════════════════════════ --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100">
      <div class="px-6 pt-6 pb-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Add New Notice</h2>
      </div>
      <form method="POST" action="{{ route('admin.notices.store') }}" enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf

        {{-- Title --}}
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Title <span class="text-red-500">*</span>
          </label>
          <input type="text" name="title" required
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            placeholder="सूचनाको शीर्षक लेख्नुहोस्…">
          @error('title')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
          @enderror
        </div>

        {{-- Body --}}
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">
            Content / Detail
            <span class="text-xs font-normal text-gray-400 ml-1">(optional)</span>
          </label>
          <textarea name="body" rows="5"
            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm
                   focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none
                   transition resize-y leading-relaxed"
            placeholder="सूचनाको विस्तृत विवरण यहाँ लेख्नुहोस्।&#10;&#10;धेरै लाइन लेख्न Enter थिच्नुहोस्।"></textarea>
        </div>

        {{-- Link --}}
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Notice Image / PDF</label>
          <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">
          <p class="text-[11px] text-gray-400 mt-1">Use this for image-only notices. JPG, PNG, WEBP, PDF up to 5MB.</p>
        </div>

        {{-- Link --}}
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Link URL</label>
            <input type="url" name="link"
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                     focus:ring-2 focus:ring-blue-500 outline-none transition"
              placeholder="https://…">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Link Label</label>
            <input type="text" name="link_text" value="हेर्नुहोस्"
              class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                     focus:ring-2 focus:ring-blue-500 outline-none transition">
          </div>
        </div>

        {{-- Active --}}
        <div class="flex items-center pt-1">
          <label class="flex items-center gap-3 cursor-pointer select-none">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" checked
                   class="w-4 h-4 rounded accent-blue-600">
            <div>
              <span class="text-sm font-semibold text-gray-700">Active</span>
              <p class="text-[11px] text-gray-400 leading-none mt-0.5">Show on site</p>
            </div>
          </label>
        </div>

        <button type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold
                 py-3 rounded-xl transition text-sm mt-2">
          Publish Notice
        </button>
      </form>
    </div>

    {{-- ══════════════════════════════
         Notice List  (3 cols)
    ══════════════════════════════ --}}
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100">
      <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-semibold text-gray-800">All Notices</h2>
        <span class="text-xs font-bold text-gray-400 bg-gray-100 rounded-full px-2.5 py-1">
          {{ $notices->count() }} total
        </span>
      </div>

      <div class="divide-y divide-gray-50 max-h-[640px] overflow-y-auto">
        @forelse($notices as $notice)
        <div class="px-6 py-4 hover:bg-gray-50/60 transition-colors">
          <div class="flex items-start gap-4">

            {{-- Date column --}}
            <div class="shrink-0 text-center min-w-[48px]">
              <div class="text-lg font-extrabold text-gray-800 leading-none">
                {{ $notice->created_at->format('d') }}
              </div>
              <div class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mt-0.5">
                {{ $notice->created_at->format('M Y') }}
              </div>
            </div>

            {{-- Divider --}}
            <div class="w-px self-stretch bg-gray-200 shrink-0"></div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
              <div class="flex items-start justify-between gap-2">
                <h3 class="font-semibold text-gray-800 text-sm leading-snug">{{ $notice->title }}</h3>
                <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full
                  {{ $notice->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                  {{ $notice->is_active ? 'Active' : 'Off' }}
                </span>
              </div>

              @if($notice->body)
              <p class="text-xs text-gray-500 mt-1.5 leading-relaxed line-clamp-2">{{ $notice->body }}</p>
              @endif

              @if($notice->link)
              <p class="text-xs text-blue-500 mt-1 truncate">
                <i class="fa-solid fa-link text-[10px] mr-0.5"></i> {{ $notice->link }}
              </p>
              @endif
              @if($notice->attachment_path)
              <p class="text-xs text-amber-600 mt-1 truncate">
                <i class="fa-solid fa-paperclip text-[10px] mr-0.5"></i> {{ $notice->attachment_name }}
              </p>
              @endif

              {{-- Actions --}}
              <div class="flex items-center gap-2 mt-2.5">
                <button
                  onclick="openEdit(
                    {{ $notice->id }},
                    {{ json_encode($notice->title) }},
                    {{ json_encode($notice->body ?? '') }},
                    {{ json_encode($notice->link ?? '') }},
                    {{ json_encode($notice->link_text ?? '') }},
                    {{ $notice->is_active ? 'true' : 'false' }},
                    {{ json_encode($notice->attachment_name ?? '') }}
                  )"
                  class="text-xs px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg
                         hover:bg-blue-100 font-medium transition-colors">
                  Edit
                </button>
                <form method="POST" action="{{ route('admin.notices.destroy', $notice) }}"
                      onsubmit="return confirm('Delete this notice?')">
                  @csrf @method('DELETE')
                  <button class="text-xs px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg
                                 hover:bg-rose-100 font-medium transition-colors">
                    Delete
                  </button>
                </form>
              </div>
            </div>

          </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center text-gray-400">
          <i class="fa-solid fa-bell-slash text-3xl mb-3 text-gray-200"></i>
          <p class="text-sm font-medium">No notices yet.</p>
        </div>
        @endforelse
      </div>
    </div>

  </div>
</div>

{{-- ══════════════════════════════════════
     Edit Modal
══════════════════════════════════════ --}}
<div id="editModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" style="display:none">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">

    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
      <h3 class="font-bold text-gray-800 text-lg">Edit Notice</h3>
      <button onclick="closeEdit()"
        class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center
               text-gray-400 hover:text-gray-600 transition-colors text-lg leading-none">
        ✕
      </button>
    </div>

    <form id="editForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
      @csrf @method('PUT')

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Title *</label>
        <input type="text" name="title" id="eTitle" required
          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm
                 focus:ring-2 focus:ring-blue-500 outline-none transition">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Content / Detail</label>
        <textarea name="body" id="eBody" rows="5"
          class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm
                 focus:ring-2 focus:ring-blue-500 outline-none transition resize-y leading-relaxed"
          placeholder="सूचनाको विस्तृत विवरण…"></textarea>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Link URL</label>
          <input type="url" name="link" id="eLink"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm outline-none transition">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Link Label</label>
          <input type="text" name="link_text" id="eLinkText"
            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm outline-none transition">
        </div>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Replace Notice Image / PDF</label>
        <input type="file" name="attachment" accept="image/jpeg,image/png,image/webp,application/pdf"
          class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm outline-none transition">
        <label id="eRemoveAttachmentWrap" class="hidden mt-2 items-center gap-2 text-xs text-rose-600">
          <input type="checkbox" name="remove_attachment" value="1" class="rounded">
          Remove current file: <span id="eAttachmentName" class="font-semibold"></span>
        </label>
      </div>

      <div class="flex items-center">
        <label class="flex items-center gap-3 cursor-pointer">
          <input type="hidden" name="is_active" value="0">
          <input type="checkbox" name="is_active" id="eIsActive" value="1"
                 class="w-4 h-4 rounded accent-blue-600">
          <span class="text-sm font-semibold text-gray-700">Active</span>
        </label>
      </div>

      <div class="flex gap-3 pt-1">
        <button type="submit"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl text-sm transition">
          Save Changes
        </button>
        <button type="button" onclick="closeEdit()"
          class="flex-1 border border-gray-200 text-gray-600 hover:bg-gray-50 py-3 rounded-xl text-sm font-medium transition">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, title, body, link, linkText, isActive, attachmentName = '') {
  document.getElementById('editForm').action = '/admin/notices/' + id;
  document.getElementById('eTitle').value    = title;
  document.getElementById('eBody').value     = body;
  document.getElementById('eLink').value     = link;
  document.getElementById('eLinkText').value = linkText;
  document.getElementById('eIsActive').checked = isActive;
  document.getElementById('eAttachmentName').textContent = attachmentName;
  document.getElementById('eRemoveAttachmentWrap').style.display = attachmentName ? 'flex' : 'none';
  document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() {
  document.getElementById('editModal').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') closeEdit();
});
</script>
@endsection
