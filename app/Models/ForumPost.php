<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    public function forum()
    {
        return $this->belongsTo(Forum::class);
    }
}
