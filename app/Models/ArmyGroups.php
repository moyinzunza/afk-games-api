<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmyGroups extends Model
{
    protected $table = 'army_groups';

    protected $fillable = [ 
        'user_id',
        'group_id',
        'army_id',
        'qty'
    ];

}
