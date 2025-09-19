<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentChildEdge extends Model
{
    protected $fillable = ['parent_id','child_id','relation_type','notes'];

    public function parent() { return $this->belongsTo(Person::class, 'parent_id'); }
    public function child() { return $this->belongsTo(Person::class, 'child_id'); }
}
