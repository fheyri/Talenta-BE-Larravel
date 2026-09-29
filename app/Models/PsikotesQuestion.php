<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesQuestion extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function test()
    {
        return $this->belongsTo(PsikotesTest::class, 'test_id');
    }

    public function options()
    {
        return $this->hasMany(PsikotesOption::class, 'question_id');
    }
}
