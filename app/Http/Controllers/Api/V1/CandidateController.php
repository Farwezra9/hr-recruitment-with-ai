<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\CandidateCvController;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends ApiController
{
    public function index(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        $query = Candidate::query()
            ->when($term !== '', fn ($q) => $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%");
            }))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->query('source')))
            ->latest();

        return CandidateResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(Candidate $candidate)
    {
        $candidate->load('applications.jobPosting');

        return new CandidateResource($candidate);
    }

    /** GET /candidates/{id}/cv: unduh CV (otorisasi sama seperti di web). */
    public function cv(Request $request, Candidate $candidate)
    {
        return app(CandidateCvController::class)($request, $candidate);
    }
}
