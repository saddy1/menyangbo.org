@extends('admin.layout')
@section('title', 'Pages')

@section('content')
<div class="py-6">
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-gray-800">Pages</h1>
  <a href="{{ route('admin.pages.create') }}"
    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
    + New Page
  </a>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
      <tr>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Title</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Slug</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Status</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Popup</th>
        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($pages as $page)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 font-medium text-gray-800">{{ $page->title }}</td>
          <td class="px-4 py-3 text-gray-500 font-mono text-xs">
            <a href="{{ route('page.show', $page->slug) }}" target="_blank"
              class="text-blue-600 hover:underline">/page/{{ $page->slug }}</a>
          </td>
          <td class="px-4 py-3">
            @if($page->is_published)
              <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Published</span>
            @else
              <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Draft</span>
            @endif
          </td>
          <td class="px-4 py-3">
            @if($page->show_in_popup)
              <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">Popup</span>
            @else
              <span class="text-xs text-gray-400">—</span>
            @endif
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('admin.pages.edit', $page) }}"
              class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 mr-1">Edit</a>
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline"
              onsubmit="return confirm('Delete this page?')">
              @csrf @method('DELETE')
              <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 rounded hover:bg-rose-200">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No pages yet. Create your first page.</td></tr>
      @endforelse
    </tbody>
  </table>
  @if($pages->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $pages->links() }}</div>
  @endif
</div>
</div>
@endsection
