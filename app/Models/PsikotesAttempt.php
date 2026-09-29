<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesAttempt extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function test()
    {
        return $this->belongsTo(PsikotesTest::class, 'test_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function answers()
    {
        return $this->hasMany(PsikotesAnswer::class, 'attempt_id');
    }
}
