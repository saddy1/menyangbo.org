<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'label', 'type', 'url', 'page_id', 'parent_id',
        'sort_order', 'is_active', 'target',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('sort_order');
    }

    public function getHrefAttribute(): string
    {
        if ($this->type === 'page' && $this->page) {
            return route('page.show', $this->page->slug);
        }
        return $this->url ?? '#';
    }

    public static function activeTopLevel()
    {
        return static::with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
