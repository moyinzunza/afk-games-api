<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modules extends Model
{
    protected $table = 'modules';

    protected $fillable = [ 
        'user_id',
        'name',
        'position_x',
        'position_y',
        'position_z',
        'construction_space',
        'resources_1',
        'resources_2',
        'resources_3',
        'resources_building_lvl_1',
        'resources_building_lvl_2',
        'resources_building_lvl_3',
        'image_url'
    ];
}
