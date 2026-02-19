<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotAttachment extends Model
{
    protected $fillable = [
        'bot_message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function user()
    {
        return $this->belongsTo(BotUser::class, 'bot_message_id');
    }
}