<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;

class GalleryController extends Controller
{
    public function index()
    {
        $media = Media::latest()->paginate(24);
        return view('admin.gallery.index', compact('media'));
    }
}
