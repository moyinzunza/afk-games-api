<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersArmy extends Model
{

    protected $fillable = [ 
        'user_id',
        'module_id',
        'army_id',
        'qty'
    ];

    protected $table = 'users_army';
}
