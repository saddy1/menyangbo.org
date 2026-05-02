<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NepaliCalendarYear extends Model
{
    protected $fillable = ['year', 'month_days'];

    protected $casts = [
        'year' => 'integer',
        'month_days' => 'array',
    ];
}
