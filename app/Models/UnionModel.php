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

    protected static function booted()
{
    static::saved(fn () => \App\Http\Controllers\Admin\PeopleTableController::forgetCache());
    // a woman marrying into the family becomes बुहारी (whichever screen added the marriage)
    static::created(function (UnionModel $u) {
        foreach ([$u->spouse1, $u->spouse2] as $spouse) {
            if ($spouse) \App\Support\MemberType::markBuhariIfMarriedIn($spouse);
        }
    });
    static::deleted(fn () => \App\Http\Controllers\Admin\PeopleTableController::forgetCache());
}
}
