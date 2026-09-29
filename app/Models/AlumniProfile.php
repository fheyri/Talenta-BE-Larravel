<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniProfile extends Model
{

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function careerHistories()
    {
        return $this->hasMany(CareerHistory::class, 'alumni_id');
    }

    public function mentoringRequests()
    {
        return $this->hasMany(MentoringRequest::class, 'alumni_id');
    }
}
