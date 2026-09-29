<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesTest extends Model
{

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(PsikotesQuestion::class, 'test_id')->orderBy('urutan');
    }

    public function resultRanges()
    {
        return $this->hasMany(PsikotesResultRange::class, 'test_id');
    }

    public function attempts()
    {
        return $this->hasMany(PsikotesAttempt::class, 'test_id');
    }
}
