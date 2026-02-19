<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMessage extends Model
{
    protected $fillable = [
        'bot_user_id',
        'direction',
        'message_text',
        'payload',
    ];

    public function user()
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }
}