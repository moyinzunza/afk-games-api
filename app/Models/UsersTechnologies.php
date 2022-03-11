<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersTechnologies extends Model
{

    protected $fillable = [ 
        'user_id',
        'technology_id',
        'level'
    ];

    protected $table = 'users_technologies';
}
