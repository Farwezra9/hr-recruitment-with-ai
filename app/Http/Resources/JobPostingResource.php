<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'status' => $this->status,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'description' => $this->description,
            'requirements' => $this->requirements,
            'responsibilities' => $this->responsibilities,
            'location' => $this->location,
            'employment_type' => $this->employment_type,
            'work_mode' => $this->work_mode,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'vacancies' => $this->vacancies,
            'closing_date' => $this->closing_date?->toDateString(),
            'applications_count' => $this->whenCounted('applications'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
