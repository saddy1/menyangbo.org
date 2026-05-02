<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HomeSectionController extends Controller
{
    public function index()
    {
        $grouped = HomeSection::orderBy('sort_order')->get()->groupBy('section');
        return view('admin.home_sections.index', compact('grouped'));
    }

    public function create(Request $request)
    {
        $section = $request->query('section', 'at_a_glance');
        return view('admin.home_sections.form', ['item' => null, 'section' => $section]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'section'    => ['required', Rule::in(array_keys(HomeSection::$sections))],
            'title'      => ['required', 'string', 'max:300'],
            'subtitle'   => ['nullable', 'string', 'max:300'],
            'body'       => ['nullable', 'string'],
            'image'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'link_url'   => ['nullable', 'string', 'max:500'],
            'color'      => ['nullable', Rule::in(HomeSection::$colors)],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['nullable', 'boolean'],
        ]);
        unset($data['image']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active', true);
        $data['image_path'] = $this->storeImage($request);

        HomeSection::create($data);
        return redirect()->route('admin.home-sections.index')->with('success', 'Item added.');
    }

    public function edit(HomeSection $homeSection)
    {
        return view('admin.home_sections.form', ['item' => $homeSection, 'section' => $homeSection->section]);
    }

    public function update(Request $request, HomeSection $homeSection)
    {
        $data = $request->validate([
            'section'      => ['required', Rule::in(array_keys(HomeSection::$sections))],
            'title'        => ['required', 'string', 'max:300'],
            'subtitle'     => ['nullable', 'string', 'max:300'],
            'body'         => ['nullable', 'string'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'link_label'   => ['nullable', 'string', 'max:120'],
            'link_url'     => ['nullable', 'string', 'max:500'],
            'color'        => ['nullable', Rule::in(HomeSection::$colors)],
            'sort_order'   => ['nullable', 'integer'],
            'is_active'    => ['nullable', 'boolean'],
        ]);
        unset($data['image'], $data['remove_image']);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active']  = $request->boolean('is_active');

        if ($request->boolean('remove_image') && $homeSection->image_path) {
            $this->deleteFile($homeSection->image_path);
            $data['image_path'] = null;
        }

        if ($path = $this->storeImage($request)) {
            $this->deleteFile($homeSection->image_path);
            $data['image_path'] = $path;
        }

        $homeSection->update($data);
        return redirect()->route('admin.home-sections.index')->with('success', 'Item updated.');
    }

    public function destroy(HomeSection $homeSection)
    {
        $this->deleteFile($homeSection->image_path);
        $homeSection->delete();
        return back()->with('success', 'Item deleted.');
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        $file = $request->file('image');
        $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $dest = public_path('banners');

        if (! File::exists($dest)) {
            File::makeDirectory($dest, 0755, true);
        }

        $file->move($dest, $filename);
        return 'banners/' . $filename;
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
