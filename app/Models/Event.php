<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['person_id','title','event_type','event_date','description'];

    protected $casts = ['event_date' => 'date'];

    public function person() { return $this->belongsTo(Person::class); }
}
