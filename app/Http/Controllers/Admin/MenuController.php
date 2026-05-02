<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('children', 'page')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        $pages = Page::where('is_published', true)->orderBy('title')->get();
        return view('admin.menus.index', compact('menus', 'pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'type'       => ['required', 'in:link,page'],
            'url'        => ['nullable', 'string', 'max:500'],
            'page_id'    => ['nullable', 'exists:pages,id'],
            'parent_id'  => ['nullable', 'exists:menus,id'],
            'sort_order' => ['nullable', 'integer'],
            'target'     => ['nullable', 'in:_self,_blank'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active']  = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['target']     = $data['target'] ?? '_self';

        Menu::create($data);
        return redirect()->route('admin.menus.index')->with('success', 'Menu item added.');
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $request->validate([
            'label'      => ['required', 'string', 'max:100'],
            'type'       => ['required', 'in:link,page'],
            'url'        => ['nullable', 'string', 'max:500'],
            'page_id'    => ['nullable', 'exists:pages,id'],
            'parent_id'  => ['nullable', 'exists:menus,id'],
            'sort_order' => ['nullable', 'integer'],
            'target'     => ['nullable', 'in:_self,_blank'],
            'is_active'  => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $menu->update($data);
        return redirect()->route('admin.menus.index')->with('success', 'Menu item updated.');
    }

    public function destroy(Menu $menu)
    {
        $menu->children()->delete();
        $menu->delete();
        return redirect()->route('admin.menus.index')->with('success', 'Menu item deleted.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => ['required', 'array']]);
        foreach ($request->order as $index => $id) {
            Menu::where('id', $id)->update(['sort_order' => $index]);
        }
        return response()->json(['ok' => true]);
    }
}
