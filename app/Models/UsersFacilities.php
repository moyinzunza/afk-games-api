<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersFacilities extends Model
{

    protected $fillable = [ 
        'user_id',
        'module_id',
        'facility_id',
        'level'
    ];

    protected $table = 'users_facilities';
}
