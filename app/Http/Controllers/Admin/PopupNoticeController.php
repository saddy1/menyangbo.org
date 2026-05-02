<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopupNotice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class PopupNoticeController extends Controller
{
    public function index()
    {
        // Fetch newest popups first
        $popups = PopupNotice::latest()->get();
        return view('admin.popups.index', compact('popups'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'required|image|max:5120',
            'link_url' => 'nullable|url'
        ]);

        if ($request->has('is_active') && PopupNotice::where('is_active', true)->count() >= 3) {
            return back()->withErrors(['error' => 'You can only have a maximum of 3 active popups at a time. Disable an old one first.']);
        }

        $file = $request->file('image');
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $destinationPath = public_path('popups');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        $file->move($destinationPath, $filename);

        PopupNotice::create([
            'title' => $request->title,
            'image_path' => 'popups/' . $filename,
            'link_url' => $request->link_url,
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'Popup notice added successfully.');
    }

    // NEW: Edit Method
    public function edit(PopupNotice $popup)
    {
        return view('admin.popups.edit', compact('popup'));
    }

    // NEW: Update Method
    public function update(Request $request, PopupNotice $popup)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|max:5120', // Image is optional on update
            'link_url' => 'nullable|url'
        ]);

        $isActive = $request->has('is_active');

        // Check limit only if turning from Inactive -> Active
        if ($isActive && !$popup->is_active && PopupNotice::where('is_active', true)->count() >= 3) {
            return back()->withErrors(['error' => 'Maximum 3 active popups allowed. Disable an old one first.']);
        }

        $data = [
            'title' => $request->title,
            'link_url' => $request->link_url,
            'is_active' => $isActive,
        ];

        // If a new image was uploaded
        if ($request->hasFile('image')) {
            // 1. Delete old image
            $oldPath = public_path($popup->image_path);
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }

            // 2. Save new image
            $file = $request->file('image');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('popups');
            $file->move($destinationPath, $filename);
            
            $data['image_path'] = 'popups/' . $filename;
        }

        $popup->update($data);

        return redirect()->route('admin.popups.index')->with('success', 'Popup updated successfully.');
    }

    public function destroy(PopupNotice $popup)
    {
        $filePath = public_path($popup->image_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
        $popup->delete();
        return back()->with('success', 'Popup deleted permanently.');
    }

    public function toggle(PopupNotice $popup)
    {
        if (!$popup->is_active && PopupNotice::where('is_active', true)->count() >= 3) {
            return back()->withErrors(['error' => 'Maximum 3 active popups allowed.']);
        }

        $popup->update(['is_active' => !$popup->is_active]);
        return back()->with('success', 'Popup status updated.');
    }
}