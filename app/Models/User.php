<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'is_active', 'is_profile_complete', 'token_balance',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'role' => Role::class,
        'is_active' => 'boolean',
        'is_profile_complete' => 'boolean',
        'password' => 'hashed',
    ];

    public function siswaProfile()
    {
        return $this->hasOne(SiswaProfile::class);
    }

    public function alumniProfile()
    {
        return $this->hasOne(AlumniProfile::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(JobBookmark::class);
    }

    public function jobHistories()
    {
        return $this->hasMany(JobHistory::class);
    }

    public function psikotesAttempts()
    {
        return $this->hasMany(PsikotesAttempt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function tokenTransactions()
    {
        return $this->hasMany(TokenTransaction::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [Role::Admin, Role::SuperAdmin], true);
    }
}
