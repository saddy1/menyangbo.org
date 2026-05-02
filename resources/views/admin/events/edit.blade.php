@extends('admin.layout')
@section('title', 'Edit Event')

@section('content')
<div class="py-6 max-w-2xl">
<div class="flex items-center gap-3 mb-6">
  <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:text-gray-700">← Events</a>
  <h1 class="text-2xl font-bold text-gray-800">Edit Event</h1>
</div>
<form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="space-y-5">
  @csrf @method('PUT')
  <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
      <input type="text" name="title" value="{{ old('title', $event->title) }}" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
    </div>
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
      <textarea name="description" rows="4"
        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 outline-none"
        >{{ old('description', $event->description) }}</textarea>
    </div>
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
        <input type="text" name="location" value="{{ old('location', $event->location) }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
        <input type="datetime-local" name="event_date"
          value="{{ old('event_date', $event->event_date ? $event->event_date->format('Y-m-d\TH:i') : '') }}"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
    </div>
    @if($event->photo_path)
      <div>
        <p class="text-sm font-medium text-gray-700 mb-1">Current Photo</p>
        <img src="{{ asset($event->photo_path) }}" class="h-24 rounded object-cover">
      </div>
    @endif
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Replace Photo</label>
      <input type="file" name="photo" accept="image/*"
        class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
    </div>
    <div class="flex items-center gap-6">
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" {{ $event->is_active ? 'checked' : '' }} class="rounded">
        <span class="text-sm text-gray-700">Active</span>
      </label>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="show_popup" value="1" {{ $event->show_popup ? 'checked' : '' }} class="rounded">
        <span class="text-sm text-gray-700">Show Popup</span>
      </label>
    </div>
  </div>
  <div class="flex gap-3">
    <button type="submit"
      class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg transition text-sm">Save</button>
    <a href="{{ route('admin.events.index') }}"
      class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-6 py-2.5 rounded-lg text-sm">Cancel</a>
  </div>
</form>
</div>
@endsection
