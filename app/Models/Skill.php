<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function siswa()
    {
        return $this->belongsToMany(SiswaProfile::class, 'siswa_skills', 'skill_id', 'siswa_id')->withPivot('level');
    }
}
