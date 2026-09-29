<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $guarded = [];

    public function jobs()
    {
        return $this->hasMany(JobListing::class, 'company_id');
    }
}
