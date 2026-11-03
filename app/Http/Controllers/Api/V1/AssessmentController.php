<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AssessmentQuestionResource;
use App\Http\Resources\AssessmentResource;
use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\CandidateAssessment;
use Illuminate\Http\Request;

class AssessmentController extends ApiController
{
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => 'required|in:technical,psychometric,language,case_study,other',
            'description' => 'nullable|string',
            'duration_minutes' => 'required|integer|min:5|max:240',
            'passing_score' => 'required|integer|min:0|max:100',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function index(Request $request)
    {
        $query = Assessment::withCount('questions')
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->latest();

        return AssessmentResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(Assessment $assessment)
    {
        return new AssessmentResource($assessment->load('questions')->loadCount('questions'));
    }

    public function store(Request $request)
    {
        $assessment = Assessment::create($request->validate($this->rules()));

        return (new AssessmentResource($assessment))->response()->setStatusCode(201);
    }

    public function update(Request $request, Assessment $assessment)
    {
        $rules = array_map(fn ($r) => str_starts_with($r, 'sometimes') ? $r : 'sometimes|' . $r, $this->rules());

        $assessment->update($request->validate($rules));

        return new AssessmentResource($assessment);
    }

    public function destroy(Assessment $assessment)
    {
        // Tes yang sudah pernah dikirim ke kandidat tidak boleh dihapus (datanya masih dipakai).
        if (CandidateAssessment::where('assessment_id', $assessment->id)->exists()) {
            return response()->json([
                'message' => 'Tes ini sudah pernah dikirim ke kandidat, tidak bisa dihapus. Nonaktifkan dengan PATCH is_active=false.',
            ], 409);
        }

        $assessment->delete();

        return response()->noContent();
    }

    // --- Soal ---

    protected function questionRules(bool $partial = false): array
    {
        $rules = [
            'question' => 'required|string',
            'question_type' => 'required|in:multiple_choice,essay',
            'options' => 'required_if:question_type,multiple_choice|array|max:6',
            'options.*' => 'nullable|string',
            'correct_option' => 'required_if:question_type,multiple_choice|nullable|string',
            'points' => 'required|integer|min:1|max:20',
        ];

        return $partial ? array_map(fn ($r) => 'sometimes|' . $r, $rules) : $rules;
    }

    /** options dikirim sebagai array teks berurutan -> disimpan dengan label A..F */
    protected function labelOptions(?array $options): ?array
    {
        if ($options === null) {
            return null;
        }

        $labels = ['A', 'B', 'C', 'D', 'E', 'F'];
        $result = [];

        foreach (array_values($options) as $i => $text) {
            if (filled($text) && isset($labels[$i])) {
                $result[$labels[$i]] = $text;
            }
        }

        return $result;
    }

    public function storeQuestion(Request $request, Assessment $assessment)
    {
        $data = $request->validate($this->questionRules());
        $isMc = $data['question_type'] === 'multiple_choice';
        $options = $isMc ? $this->labelOptions($data['options'] ?? []) : null;

        if ($isMc && ! isset($options[$data['correct_option'] ?? ''])) {
            return response()->json([
                'message' => 'Data tidak valid.',
                'errors' => ['correct_option' => ['Jawaban benar harus menunjuk ke opsi yang terisi (A-F).']],
            ], 422);
        }

        $question = $assessment->questions()->create([
            'question' => $data['question'],
            'question_type' => $data['question_type'],
            'options' => $options,
            'correct_option' => $isMc ? $data['correct_option'] : null,
            'points' => $data['points'],
            'order' => $assessment->questions()->count() + 1,
        ]);

        return (new AssessmentQuestionResource($question))->response()->setStatusCode(201);
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question)
    {
        $data = $request->validate($this->questionRules(partial: true));

        $type = $data['question_type'] ?? $question->question_type;
        $isMc = $type === 'multiple_choice';

        $payload = collect($data)->only(['question', 'question_type', 'points'])->all();

        if ($isMc) {
            $options = array_key_exists('options', $data) ? $this->labelOptions($data['options']) : $question->options;
            $correct = $data['correct_option'] ?? $question->correct_option;

            if (! isset($options[$correct ?? ''])) {
                return response()->json([
                    'message' => 'Data tidak valid.',
                    'errors' => ['correct_option' => ['Jawaban benar harus menunjuk ke opsi yang terisi (A-F).']],
                ], 422);
            }

            $payload += ['options' => $options, 'correct_option' => $correct];
        } else {
            $payload += ['options' => null, 'correct_option' => null];
        }

        $question->update($payload);

        return new AssessmentQuestionResource($question);
    }

    public function destroyQuestion(AssessmentQuestion $question)
    {
        $question->delete();

        return response()->noContent();
    }
}
