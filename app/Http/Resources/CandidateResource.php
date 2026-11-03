<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'linkedin_url' => $this->linkedin_url,
            'portfolio_url' => $this->portfolio_url,
            'source' => $this->source,
            'consented_at' => $this->consented_at?->toIso8601String(),
            // CV hanya bisa diunduh lewat endpoint ini (butuh token dan hak akses)
            'cv_url' => url("/api/v1/candidates/{$this->id}/cv"),
            'applications' => ApplicationResource::collection($this->whenLoaded('applications')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
