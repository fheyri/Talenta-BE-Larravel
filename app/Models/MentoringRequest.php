<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentoringRequest extends Model
{

    protected $guarded = [];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(SiswaProfile::class, 'siswa_id');
    }

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function session()
    {
        return $this->hasOne(MentoringSession::class, 'request_id');
    }
}
