<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    protected $fillable = [
        'channel',
        'channel_user_id',
        'last_received_message_timestamp',
        'is_active'
    ];
}
