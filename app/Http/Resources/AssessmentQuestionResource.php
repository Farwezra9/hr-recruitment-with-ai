<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_type' => $this->question_type,
            'options' => $this->options,
            // Kunci jawaban hanya untuk recruiter/admin
            'correct_option' => $this->when($request->user()?->isRecruiter(), $this->correct_option),
            'points' => $this->points,
            'order' => $this->order,
        ];
    }
}
