@extends('admin.layout')
@section('title', 'Edit Page')

@section('content')
<div class="py-6 max-w-3xl">
<div class="flex items-center gap-3 mb-6">
  <a href="{{ route('admin.pages.index') }}" class="text-gray-500 hover:text-gray-700">← Pages</a>
  <h1 class="text-2xl font-bold text-gray-800">Edit: {{ $page->title }}</h1>
</div>

<form method="POST" action="{{ route('admin.pages.update', $page) }}" class="space-y-5">
  @csrf @method('PUT')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Page Title *</label>
      <input type="text" name="title" value="{{ old('title', $page->title) }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
      <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">
        <span class="px-3 py-2.5 bg-gray-50 text-gray-500 text-sm border-r border-gray-300">/page/</span>
        <input type="text" name="slug" value="{{ old('slug', $page->slug) }}"
          class="flex-1 px-3 py-2.5 text-sm outline-none">
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
      <textarea name="content" rows="14"
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none font-mono"
        >{{ old('content', $page->content) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
        <input type="text" name="meta_description" value="{{ old('meta_description', $page->meta_description) }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
    </div>
    <div class="flex items-center gap-6 pt-2">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_published" value="1" {{ $page->is_published ? 'checked' : '' }} class="rounded border-gray-300">
        <span class="text-sm text-gray-700">Published</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="show_in_popup" value="1" {{ $page->show_in_popup ? 'checked' : '' }} class="rounded border-gray-300">
        <span class="text-sm text-gray-700">Show in Popup</span>
      </label>
    </div>
  </div>
  <div class="flex gap-3">
    <button type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition text-sm">
      Save Changes
    </button>
    <a href="{{ route('admin.pages.index') }}"
      class="border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold px-6 py-2.5 rounded-lg text-sm">
      Cancel
    </a>
  </div>
</form>
</div>
@endsection
