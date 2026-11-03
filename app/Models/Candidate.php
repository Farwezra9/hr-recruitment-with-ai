<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $hidden = [
        'cv_path', 'cv_raw_text',
    ];

    protected $fillable = [
        'name', 'email', 'phone', 'cv_path', 'cv_raw_text',
        'linkedin_url', 'portfolio_url', 'source',
    ];

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
