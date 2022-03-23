<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmyMovement extends Model
{
    protected $table = 'army_movement';

    protected $fillable = [ 
        'user_id',
        'user_id_destination',
        'module_id',
        'module_id_destination',
        'army_group_id',
        'type',
        'resources_1_carring',
        'resources_2_carring',
        'resources_3_carring',
        'start_at',
        'finish_at'
    ];
}
