<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['name', 'code'];

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }
}
