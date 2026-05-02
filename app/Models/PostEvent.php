<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostEvent extends Model
{
    protected $table = 'post_events';

    protected $fillable = [
        'title', 'description', 'location', 'event_date',
        'photo_path', 'is_active', 'show_popup',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'is_active'  => 'boolean',
        'show_popup' => 'boolean',
    ];
}
