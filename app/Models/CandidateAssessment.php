<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateAssessment extends Model
{
    protected $fillable = [
        'application_id', 'assessment_id', 'status', 'assigned_at',
        'started_at', 'completed_at', 'score', 'answers',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'answers' => 'array',
        ];
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }
}
