<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UpgradesLine extends Model
{
    protected $table = 'upgrades_line';

    protected $fillable = [ 
        'user_id',
        'module_id',
        'upgrade_id',
        'type',
        'finish_at'
    ];

}
