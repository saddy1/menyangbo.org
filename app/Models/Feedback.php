<?php

// app/Models/Feedback.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $fillable = ['name','email','contact','description','hp_field','ip','user_agent'];
}
