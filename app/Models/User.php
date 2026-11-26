<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'has_taken_test',
        'admin_notes',
        'phone',
        'date_of_birth',
        'education',
        'department',
        'address'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'has_taken_test' => 'boolean',
        'date_of_birth' => 'date'
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isHR()
    {
        return $this->role === 'hr';
    }

    public function kraeplinTests()
    {
        return $this->hasMany(KraeplinTest::class);
    }

    public function testSessions()
    {
        return $this->hasMany(TestSession::class);
    }
}