<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringSession extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(MentoringRequest::class, 'request_id');
    }

    public function messages()
    {
        return $this->hasMany(MentoringMessage::class, 'session_id');
    }
}
