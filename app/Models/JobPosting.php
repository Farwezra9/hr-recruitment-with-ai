<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'department_id', 'created_by', 'description', 'requirements',
        'responsibilities', 'location', 'employment_type', 'work_mode',
        'salary_min', 'salary_max', 'vacancies', 'status', 'closing_date',
    ];

    protected function casts(): array
    {
        return [
            'closing_date' => 'date',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
}
