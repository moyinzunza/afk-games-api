<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArmyLine extends Model
{
    protected $table = 'army_line';

    protected $fillable = [ 
        'user_id',
        'module_id',
        'army_id',
        'qty',
        'time_per_unit',
        'type',
        'start_at',
        'finish_at'
    ];

}
