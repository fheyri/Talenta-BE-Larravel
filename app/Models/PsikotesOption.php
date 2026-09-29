<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesOption extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(PsikotesQuestion::class, 'question_id');
    }
}
