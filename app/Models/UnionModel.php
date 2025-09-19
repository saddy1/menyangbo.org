<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnionModel extends Model
{
    protected $table = 'unions';

    protected $fillable = [
        'spouse1_id','spouse2_id','type','start_date','end_date','notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function spouse1() { return $this->belongsTo(Person::class, 'spouse1_id'); }
    public function spouse2() { return $this->belongsTo(Person::class, 'spouse2_id'); }
}
