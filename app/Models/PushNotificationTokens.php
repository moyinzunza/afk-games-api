<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushNotificationTokens extends Model
{
    protected $table = 'push_notification_tokens';

    protected $fillable = [ 
        'user_id',
        'token',
    ];
}
