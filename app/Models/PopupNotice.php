<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopupNotice extends Model
{
    protected $fillable = [
        'title', 'image_path', 'link_url', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
