<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PsikotesAnswer extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function attempt()
    {
        return $this->belongsTo(PsikotesAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(PsikotesQuestion::class, 'question_id');
    }

    public function option()
    {
        return $this->belongsTo(PsikotesOption::class, 'option_id');
    }
}
