<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PostEventController extends Controller
{
    public function index()
    {
        $events = PostEvent::orderByDesc('event_date')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location'    => ['nullable', 'string', 'max:255'],
            'event_date'  => ['nullable', 'date'],
            'is_active'   => ['nullable', 'boolean'],
            'show_popup'  => ['nullable', 'boolean'],
            'photo'       => ['nullable', 'image', 'max:5120'],
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['show_popup'] = $request->boolean('show_popup', false);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        PostEvent::create($data);
        return redirect()->route('admin.events.index')->with('success', 'Event added.');
    }

    public function edit(PostEvent $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, PostEvent $event)
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location'    => ['nullable', 'string', 'max:255'],
            'event_date'  => ['nullable', 'date'],
            'is_active'   => ['nullable', 'boolean'],
            'show_popup'  => ['nullable', 'boolean'],
            'photo'       => ['nullable', 'image', 'max:5120'],
        ]);

        $data['is_active']  = $request->boolean('is_active');
        $data['show_popup'] = $request->boolean('show_popup');

        if ($request->hasFile('photo')) {
            $this->deleteFile($event->photo_path);
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        $event->update($data);
        return redirect()->route('admin.events.index')->with('success', 'Event updated.');
    }

    public function destroy(PostEvent $event)
    {
        $this->deleteFile($event->photo_path);
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }

    private function storePhoto($file): string
    {
        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $dest = public_path('events');

        if (! File::exists($dest)) {
            File::makeDirectory($dest, 0755, true);
        }

        $file->move($dest, $filename);
        return 'events/' . $filename;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }
}
