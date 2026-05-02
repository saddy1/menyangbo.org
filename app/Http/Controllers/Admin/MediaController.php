<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    // Fetch all images for the gallery grid
    public function index()
    {
        $media = Media::latest()->get();
        return response()->json($media);
    }

    // Handle AJAX file uploads
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // Max 5MB
        ]);

        $file = $request->file('file');
        
        // 1. Generate a clean, unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::slug($originalName) . '.' . $extension;
        
        // 2. Define the destination inside the public folder
        $destinationPath = public_path('media');

        // Ensure the directory exists
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }
        
        // 3. Move the file directly to public/media
        $file->move($destinationPath, $filename);

        // The path we save to the database (relative to the public folder)
        $dbPath = 'media/' . $filename;

        // 4. Save to database
        $media = Media::create([
            'name' => $file->getClientOriginalName(),
            'file_path' => $dbPath,
            'mime_type' => $file->getClientMimeType(), // Use getClientMimeType() for moved files
            'size' => filesize(public_path($dbPath)),   // Get size from the newly moved file
        ]);

        return response()->json([
            'message' => 'File uploaded successfully to public folder',
            'media' => $media
        ]);
    }

    // Add this to the top of your controller if it isn't there already:
    // use Illuminate\Support\Facades\File;
    // use Illuminate\Support\Str;

    /**
     * Display the Admin Gallery Page
     */
    public function gallery()
    {
        // Paginate to keep the page fast if you have hundreds of images
        $media = Media::latest()->paginate(24);
        return view('admin.gallery.index', compact('media'));
    }

    /**
     * Handle Multiple File Uploads from the Gallery Page
     */
  public function uploadMultiple(Request $request)
{
    $request->validate([
        'files' => ['nullable', 'array'],
        'files.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        'titles' => ['nullable', 'array'],
        'titles.*' => ['nullable', 'string', 'max:255'],
    ]);

    $uploaded = 0;

    if (! $request->hasFile('files')) {
        return back()->with('success', 'No photos selected.');
    }

    if ($request->hasFile('files')) {
        $destinationPath = public_path('media');

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0755, true);
        }

        foreach ($request->file('files') as $index => $file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $safeName = Str::slug($originalName) ?: 'gallery-photo';
            $filename = now()->format('YmdHis') . '_' . $safeName . '_' . uniqid() . '.' . $extension;
            $title = trim((string) $request->input("titles.$index", ''));
            
            $file->move($destinationPath, $filename);
            $dbPath = 'media/' . $filename;

            Media::create([
                'name' => $file->getClientOriginalName(),
                'title' => $title !== '' ? $title : $originalName,
                'file_path' => $dbPath,
                'mime_type' => $file->getClientMimeType(),
                'size' => filesize(public_path($dbPath)),
                'category' => null,
            ]);

            $uploaded++;
        }
    }

    return back()->with('success', $uploaded . ' photo' . ($uploaded === 1 ? '' : 's') . ' uploaded successfully.');
}
    /**
     * Delete an Image from Storage and Database
     */
    public function destroy(Media $media)
    {
        // 1. Delete the physical file from the public folder
        $filePath = public_path($media->file_path);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        // 2. Delete the database record
        $media->delete();

        return back()->with('success', 'Image permanently deleted.');
    }
}
