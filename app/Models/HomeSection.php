<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'section',
        'title',
        'subtitle',
        'body',
        'image_path',
        'link_label',
        'link_url',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public static array $sections = [
        'banner'      => 'Banner',
        'at_a_glance' => 'एक नजरमा',
        'timeline'    => 'घटनाक्रम',
        'key_figures' => 'प्रमुख व्यक्तित्व',
        'notes'       => 'नोटहरू',
    ];

    public static array $colors = [
        'emerald', 'sky', 'rose', 'fuchsia', 'amber', 'teal', 'blue', 'indigo', 'purple', 'orange',
    ];

    public function bullets(): array
    {
        if (!$this->body) return [];
        return array_values(array_filter(array_map('trim', explode("\n", $this->body))));
    }
}
