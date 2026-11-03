<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CandidateAssessment extends Model
{
    protected $fillable = [
        'application_id', 'assessment_id', 'token', 'status', 'assigned_at', 'expires_at',
        'started_at', 'completed_at', 'score', 'answers', 'essay_scores', 'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'expires_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'graded_at' => 'datetime',
            'answers' => 'array',
            'essay_scores' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->token ??= Str::random(40);
            $model->expires_at ??= now()->addDays(7);
        });
    }

    /** Link tes memakai token acak, bukan ID angka yang mudah ditebak. */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'expired'], true);
    }

    /** Sudah dikerjakan, punya soal esai, tapi belum dinilai recruiter. */
    public function needsGrading(): bool
    {
        return $this->isFinished()
            && $this->graded_at === null
            && $this->assessment->questions->contains('question_type', 'essay');
    }
}
