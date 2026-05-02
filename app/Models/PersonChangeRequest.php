<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'person_id',
        'type',
        'payload',
        'status',
        'submitted_name',
        'submitted_email',
        'submitted_mobile',
        'submitted_note',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'payload'     => 'array',
        'reviewed_at' => 'datetime',   // ✅ fixes format() on string
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}