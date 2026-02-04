<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSession extends Model
{
    protected $fillable = [
        'bot_user_id',
        'current_command',
        'current_step',
        'session_data',
        'expires_at'
    ];

    protected $casts = [
        'session_data' => 'array',
        'expires_at' => 'datetime'
    ];

    public function botUser()
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }

}
