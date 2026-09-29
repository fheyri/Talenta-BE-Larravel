<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPackage extends Model
{

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'harga' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'package_id');
    }
}
