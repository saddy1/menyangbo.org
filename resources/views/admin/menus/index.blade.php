@extends('admin.layout')
@section('title', 'Menu Management')

@section('content')
<div class="py-6">
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-gray-800">Menu Management</h1>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  {{-- ── Add Menu Item ── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-700 mb-4">Add Menu Item</h2>
    <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Label *</label>
        <input type="text" name="label" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select name="type" id="menuType"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="link">Custom Link</option>
            <option value="page">Page</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
          <select name="target"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="_self">Same tab</option>
            <option value="_blank">New tab</option>
          </select>
        </div>
      </div>
      <div id="urlField">
        <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
        <input type="text" name="url" placeholder="https://... or /path"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div id="pageField" class="hidden">
        <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
        <select name="page_id"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
          <option value="">— Select Page —</option>
          @foreach($pages as $page)
            <option value="{{ $page->id }}">{{ $page->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Parent (Dropdown)</label>
          <select name="parent_id"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">— Top Level —</option>
            @foreach($menus as $parent)
              <option value="{{ $parent->id }}">{{ $parent->label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
          <input type="number" name="sort_order" value="0" min="0"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
        <span class="text-sm text-gray-700">Active</span>
      </label>
      <button type="submit"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg transition text-sm">
        Add Menu Item
      </button>
    </form>
  </div>

  {{-- ── Menu List ── --}}
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-700 mb-4">Current Menu Structure</h2>
    @forelse($menus as $menu)
      <div class="border border-gray-200 rounded-lg mb-3 overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
          <div class="flex items-center gap-2">
            <span class="font-medium text-sm text-gray-800">{{ $menu->label }}</span>
            @if(!$menu->is_active)
              <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">Inactive</span>
            @endif
            <span class="text-xs text-gray-400">{{ $menu->type === 'page' ? '(Page)' : $menu->url }}</span>
          </div>
          <div class="flex gap-2">
            <button onclick="openEdit({{ $menu->id }}, '{{ addslashes($menu->label) }}', '{{ $menu->type }}', '{{ addslashes($menu->url ?? '') }}', {{ $menu->page_id ?? 'null' }}, {{ $menu->parent_id ?? 'null' }}, {{ $menu->sort_order }}, {{ $menu->is_active ? 'true' : 'false' }}, '{{ $menu->target }}')"
              class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">Edit</button>
            <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" onsubmit="return confirm('Delete this menu?')">
              @csrf @method('DELETE')
              <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 rounded hover:bg-rose-200">Delete</button>
            </form>
          </div>
        </div>
        @if($menu->children->count())
          <div class="px-4 py-2 space-y-1 bg-white border-t border-gray-100">
            @foreach($menu->children as $child)
              <div class="flex items-center justify-between py-1.5 px-3 bg-slate-50 rounded">
                <span class="text-sm text-gray-700 flex items-center gap-1">
                  <span class="text-gray-400">└</span> {{ $child->label }}
                  @if(!$child->is_active)<span class="text-xs text-gray-400">(hidden)</span>@endif
                </span>
                <div class="flex gap-2">
                  <button onclick="openEdit({{ $child->id }}, '{{ addslashes($child->label) }}', '{{ $child->type }}', '{{ addslashes($child->url ?? '') }}', {{ $child->page_id ?? 'null' }}, {{ $child->parent_id ?? 'null' }}, {{ $child->sort_order }}, {{ $child->is_active ? 'true' : 'false' }}, '{{ $child->target }}')"
                    class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded hover:bg-blue-200">Edit</button>
                  <form method="POST" action="{{ route('admin.menus.destroy', $child) }}" onsubmit="return confirm('Delete?')">
                    @csrf @method('DELETE')
                    <button class="text-xs px-2 py-0.5 bg-rose-100 text-rose-700 rounded hover:bg-rose-200">Del</button>
                  </form>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @empty
      <p class="text-sm text-gray-400 text-center py-8">No menu items yet.</p>
    @endforelse
  </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 relative">
    <h3 class="font-semibold text-lg mb-4">Edit Menu Item</h3>
    <form id="editForm" method="POST" class="space-y-4">
      @csrf @method('PUT')
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Label *</label>
        <input type="text" name="label" id="eLabel" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
          <select name="type" id="eType"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="link">Custom Link</option>
            <option value="page">Page</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
          <select name="target" id="eTarget"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="_self">Same tab</option>
            <option value="_blank">New tab</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
        <input type="text" name="url" id="eUrl"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Page</label>
        <select name="page_id" id="ePageId"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
          <option value="">— Select Page —</option>
          @foreach($pages as $page)
            <option value="{{ $page->id }}">{{ $page->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Parent</label>
          <select name="parent_id" id="eParentId"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
            <option value="">— Top Level —</option>
            @foreach($menus as $parent)
              <option value="{{ $parent->id }}">{{ $parent->label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Order</label>
          <input type="number" name="sort_order" id="eSortOrder" min="0"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm outline-none">
        </div>
      </div>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" id="eIsActive" value="1" class="rounded">
        <span class="text-sm text-gray-700">Active</span>
      </label>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg text-sm">Save Changes</button>
        <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
          class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2 rounded-lg text-sm">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id, label, type, url, pageId, parentId, sortOrder, isActive, target) {
  document.getElementById('editForm').action = '/admin/menus/' + id;
  document.getElementById('eLabel').value = label;
  document.getElementById('eType').value = type;
  document.getElementById('eUrl').value = url;
  document.getElementById('ePageId').value = pageId || '';
  document.getElementById('eParentId').value = parentId || '';
  document.getElementById('eSortOrder').value = sortOrder;
  document.getElementById('eIsActive').checked = isActive;
  document.getElementById('eTarget').value = target;
  document.getElementById('editModal').classList.remove('hidden');
}
// Toggle url/page fields
document.getElementById('menuType').addEventListener('change', function() {
  document.getElementById('urlField').classList.toggle('hidden', this.value === 'page');
  document.getElementById('pageField').classList.toggle('hidden', this.value !== 'page');
});
</script>
</div>
@endsection
