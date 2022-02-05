<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FailedPushNotifications extends Model
{
    protected $table = 'failed_push_notitications';

    protected $fillable = [ 
        'user_id',
        'title',
        'body',
        'token',
        'error',
    ];
}
