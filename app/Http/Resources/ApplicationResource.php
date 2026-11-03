<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage' => $this->stage,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'ai' => [
                'score' => $this->ai_score,
                'recommendation' => $this->ai_recommendation,
                'summary' => $this->ai_summary,
                'analysis' => $this->ai_analysis,
                'screened_at' => $this->ai_screened_at?->toIso8601String(),
            ],
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),
            'job' => new JobPostingResource($this->whenLoaded('jobPosting')),
            'interviews' => InterviewResource::collection($this->whenLoaded('interviews')),
            'assessments' => CandidateAssessmentResource::collection($this->whenLoaded('candidateAssessments')),
            'history' => StageHistoryResource::collection($this->whenLoaded('stageHistories')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
