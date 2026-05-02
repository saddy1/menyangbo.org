<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommitteeMember extends Model
{
    protected $fillable = ['committee_id', 'name', 'position', 'photo_path', 'email', 'contact', 'sort_order'];

    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset($this->photo_path) : null;
    }
}
