@extends('admin.layout')
@section('title', 'Events')

@section('content')
<div class="py-6">
<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-bold text-gray-800">Events Management</h1>
  <button onclick="document.getElementById('addEventModal').classList.remove('hidden')"
    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition">
    + Add Event
  </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 border-b border-gray-200">
      <tr>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Event</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Date</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Location</th>
        <th class="text-left px-4 py-3 font-semibold text-gray-700">Status</th>
        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
      @forelse($events as $event)
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3">
            <div class="font-medium text-gray-800">{{ $event->title }}</div>
            @if($event->description)
              <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $event->description }}</div>
            @endif
          </td>
          <td class="px-4 py-3 text-gray-600">{{ $event->event_date ? $event->event_date->format('Y-m-d') : '—' }}</td>
          <td class="px-4 py-3 text-gray-600">{{ $event->location ?? '—' }}</td>
          <td class="px-4 py-3">
            <div class="flex flex-col gap-1">
              @if($event->is_active)
                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full w-fit">Active</span>
              @else
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full w-fit">Inactive</span>
              @endif
              @if($event->show_popup)
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full w-fit">Popup</span>
              @endif
            </div>
          </td>
          <td class="px-4 py-3 text-right">
            <a href="{{ route('admin.events.edit', $event) }}"
              class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 mr-1">Edit</a>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline"
              onsubmit="return confirm('Delete this event?')">
              @csrf @method('DELETE')
              <button class="text-xs px-2 py-1 bg-rose-100 text-rose-700 rounded hover:bg-rose-200">Delete</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No events yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  @if($events->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">{{ $events->links() }}</div>
  @endif
</div>

{{-- Add Event Modal --}}
<div id="addEventModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h3 class="font-semibold text-lg mb-4">Add Event</h3>
    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
        <input type="text" name="title" required
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="3"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <input type="text" name="location"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
          <input type="datetime-local" name="event_date"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photo</label>
        <input type="file" name="photo" accept="image/*"
          class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-50 file:text-blue-700">
      </div>
      <div class="flex items-center gap-6">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="is_active" value="1" checked class="rounded">
          <span class="text-sm text-gray-700">Active</span>
        </label>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="show_popup" value="1" class="rounded">
          <span class="text-sm text-gray-700">Show Popup</span>
        </label>
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-lg text-sm">Add Event</button>
        <button type="button" onclick="document.getElementById('addEventModal').classList.add('hidden')"
          class="flex-1 border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 rounded-lg text-sm">Cancel</button>
      </div>
    </form>
  </div>
</div>

</div>
@endsection
