<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringMessage extends Model
{
    const UPDATED_AT = null;

    protected $guarded = [];

    public function session()
    {
        return $this->belongsTo(MentoringSession::class, 'session_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
