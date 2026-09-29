<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaProfile extends Model
{

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'siswa_skills', 'siswa_id', 'skill_id')->withPivot('level');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'siswa_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'siswa_id');
    }

    public function mentoringRequests()
    {
        return $this->hasMany(MentoringRequest::class, 'siswa_id');
    }
}
