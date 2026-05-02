<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'title',
        'body',
        'attachment_path',
        'attachment_type',
        'attachment_name',
        'link',
        'link_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getAttachmentUrlAttribute(): ?string
    {
        if (!$this->attachment_path) {
            return null;
        }

        return asset($this->attachment_path);
    }

    public function getDisplayDateTimeAttribute(): string
    {
        return $this->created_at->format('d M Y');
    }

    public function scopeSorted($query)
    {
        return $query->orderByDesc('created_at');
    }

    // All active notices sorted newest-first, used for the public page
    public static function active()
    {
        return static::where('is_active', true)
            ->sorted()
            ->get();
    }

    // Last 4 active notices for the sticky marquee bar
    public static function forMarquee()
    {
        return static::where('is_active', true)
            ->sorted()
            ->limit(4)
            ->get();
    }
}
