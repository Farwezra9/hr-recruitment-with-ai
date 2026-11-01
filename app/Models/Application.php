<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'candidate_id', 'job_posting_id', 'stage', 'status',
        'ai_score', 'ai_summary', 'ai_analysis', 'ai_screened_at', 'ai_recommendation',
        'rejected_by', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'ai_analysis' => 'array',
            'ai_screened_at' => 'datetime',
        ];
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function stageHistories()
    {
        return $this->hasMany(ApplicationStageHistory::class)->latest();
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function candidateAssessments()
    {
        return $this->hasMany(CandidateAssessment::class);
    }

    public function moveToStage(string $stage, ?int $userId = null, ?string $notes = null): void
    {
        $from = $this->stage;
        $this->update(['stage' => $stage]);

        $this->stageHistories()->create([
            'from_stage' => $from,
            'to_stage' => $stage,
            'changed_by' => $userId,
            'notes' => $notes,
        ]);
    }
}
