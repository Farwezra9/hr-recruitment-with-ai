<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $canManage = (bool) $request->user()?->isRecruiter();
        $passing = $this->assessment?->passing_score;

        return [
            // Identitas di URL API memakai "token" (bukan id angka)
            'token' => $this->token,
            'application_id' => $this->application_id,
            'assessment' => new AssessmentResource($this->whenLoaded('assessment')),
            'status' => $this->status,
            'score' => $this->score,
            'passed' => $this->score !== null && $passing !== null ? $this->score >= $passing : null,
            'needs_grading' => $this->when($this->relationLoaded('assessment'), fn () => $this->needsGrading()),
            'take_url' => $this->when($canManage && ! $this->isFinished(), fn () => route('assessments.take', $this->resource)),
            'answers' => $this->when($canManage, $this->answers),
            'essay_scores' => $this->when($canManage, $this->essay_scores),
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'graded_at' => $this->graded_at?->toIso8601String(),
        ];
    }
}
