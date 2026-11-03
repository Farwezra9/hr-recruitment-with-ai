<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'avatar', 'is_active',
        'invite_token', 'invited_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'invite_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invited_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Admin + recruiter: boleh mengubah data rekrutmen. */
    public function isRecruiter(): bool
    {
        return in_array($this->role, ['admin', 'recruiter'], true);
    }

    /** Admin, recruiter, hiring manager: boleh melihat data rekrutmen. */
    public function isStaff(): bool
    {
        return in_array($this->role, ['admin', 'recruiter', 'hiring_manager'], true);
    }

    public function isInterviewer(): bool
    {
        return $this->role === 'interviewer';
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class, 'created_by');
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class, 'interviewer_id');
    }
}
