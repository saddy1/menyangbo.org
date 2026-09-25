<?php

namespace App\Http\Controllers;

use App\Models\Media;

class GalleryController extends Controller
{
    public function index()
    {
        $media = Media::latest()->paginate(24)->appends(['lang' => \App\Support\FrontendLocale::locale()]);

        return view('gallery.index', compact('media'));
    }
}
