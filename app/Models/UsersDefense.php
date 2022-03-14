<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersDefense extends Model
{

    protected $fillable = [ 
        'user_id',
        'module_id',
        'defense_id',
        'qty'
    ];

    protected $table = 'users_defense';
}
