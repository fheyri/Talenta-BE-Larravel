<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesResultRange extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function test()
    {
        return $this->belongsTo(PsikotesTest::class, 'test_id');
    }
}
