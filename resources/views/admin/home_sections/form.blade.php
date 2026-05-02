@extends('admin.layout')
@section('title', $item ? 'Edit Item' : 'Add Item')

@section('content')
@php $isBanner = old('section', $section) === 'banner'; @endphp
<div class="py-6 max-w-2xl mx-auto">

  <div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.home-sections.index') }}"
       class="text-gray-400 hover:text-gray-600">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <h1 class="text-xl font-bold text-gray-900">
      {{ $item ? 'Edit Item' : 'Add Item' }}
    </h1>
  </div>

  <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">

    <form method="POST"
          enctype="multipart/form-data"
          action="{{ $item ? route('admin.home-sections.update', $item) : route('admin.home-sections.store') }}">
      @csrf
      @if($item) @method('PUT') @endif

      {{-- Section --}}
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Section <span class="text-red-500">*</span></label>
        <select name="section" class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
          @foreach(\App\Models\HomeSection::$sections as $key => $label)
          <option value="{{ $key }}" {{ old('section', $section) === $key ? 'selected' : '' }}>
            {{ $label }} ({{ $key }})
          </option>
          @endforeach
        </select>
        @error('section') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Title --}}
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Title <span class="text-red-500">*</span>
          <span class="text-xs font-normal text-gray-400 ml-1">(person name / event heading / label)</span>
        </label>
        <input type="text" name="title" value="{{ old('title', $item?->title) }}"
               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
               placeholder="e.g. थिन्दोलुङ खोयाहाङ">
        @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Subtitle --}}
      <div class="mb-4 {{ $isBanner ? 'hidden' : '' }}">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Subtitle / Badge
          <span class="text-xs font-normal text-gray-400 ml-1">(role, date badge, or key label)</span>
        </label>
        <input type="text" name="subtitle" value="{{ old('subtitle', $item?->subtitle) }}"
               class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none"
               placeholder="e.g. मूल पुर्खा / ई.पू. ३०० / मूल थलो">
        @error('subtitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Body --}}
      <div class="mb-4 {{ $isBanner ? 'hidden' : '' }}">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Body / Description
          <span class="text-xs font-normal text-gray-400 ml-1">(for bullet points, put each point on a new line)</span>
        </label>
        <textarea name="body" rows="5"
                  class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-y"
                  placeholder="Line 1 bullet&#10;Line 2 bullet&#10;...">{{ old('body', $item?->body) }}</textarea>
        @error('body') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Banner media + link --}}
      <div class="mb-4 rounded-2xl border border-blue-100 bg-blue-50/40 p-4">
        <div class="flex items-start justify-between gap-3 mb-3">
          <div>
            <h2 class="text-sm font-bold text-gray-800">Banner Options</h2>
            <p class="text-xs text-gray-500 mt-0.5">Used mainly when Section is <strong>Banner</strong>. Image is optional.</p>
          </div>
          <span class="text-[11px] rounded-full bg-white border border-blue-100 px-2 py-1 text-blue-700">Max 1 MB</span>
        </div>

        @if($item?->image_path)
          <div class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white">
            <img src="{{ asset($item->image_path) }}" alt="{{ $item->title }}" class="h-32 w-full object-cover">
          </div>
          <label class="mb-3 flex items-center gap-2 text-xs text-red-600">
            <input type="checkbox" name="remove_image" value="1" class="rounded">
            Remove current image
          </label>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="sm:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Banner Image</label>
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none">
            @error('image') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Button Label</label>
            <input type="text" name="link_label" value="{{ old('link_label', $item?->link_label) }}"
                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="e.g. Read more">
            @error('link_label') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Button Link</label>
            <input type="text" name="link_url" value="{{ old('link_url', $item?->link_url) }}"
                   class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-400 focus:outline-none"
                   placeholder="/notices or https://...">
            @error('link_url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
          </div>
        </div>
      </div>

      {{-- Color (key_figures only, but shows for all) --}}
      <div class="mb-4">
        <label class="block text-sm font-semibold text-gray-700 mb-1">
          Color <span class="text-xs font-normal text-gray-400 ml-1">(accent color — mainly for Key Figures cards)</span>
        </label>
        <div class="flex flex-wrap gap-2">
          @foreach(\App\Models\HomeSection::$colors as $c)
          <label class="cursor-pointer">
            <input type="radio" name="color" value="{{ $c }}"
                   class="sr-only peer"
                   {{ old('color', $item?->color) === $c ? 'checked' : '' }}>
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border-2 text-xs font-semibold transition
              peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300 bg-{{ $c }}-50 text-{{ $c }}-700">
              {{ $c }}
            </span>
          </label>
          @endforeach
          <label class="cursor-pointer">
            <input type="radio" name="color" value=""
                   class="sr-only peer"
                   {{ old('color', $item?->color) === null || old('color', $item?->color) === '' ? 'checked' : '' }}>
            <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border-2 text-xs font-semibold transition
              peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300">
              none
            </span>
          </label>
        </div>
        @error('color') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
      </div>

      {{-- Sort order + active --}}
      <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Sort Order</label>
          <input type="number" name="sort_order" value="{{ old('sort_order', $item?->sort_order ?? 0) }}"
                 class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Active</label>
          <label class="flex items-center gap-2 mt-2 cursor-pointer">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   class="w-4 h-4 rounded accent-blue-600"
                   {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }}>
            <span class="text-sm text-gray-600">Show on home page</span>
          </label>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <button type="submit"
                class="px-5 py-2.5 bg-blue-600 text-white font-semibold rounded-xl text-sm hover:bg-blue-700 transition">
          {{ $item ? 'Update' : 'Add Item' }}
        </button>
        <a href="{{ route('admin.home-sections.index') }}"
           class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
          Cancel
        </a>
      </div>

    </form>
  </div>
</div>
@endsection
