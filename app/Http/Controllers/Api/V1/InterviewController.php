<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\InterviewResource;
use App\Mail\InterviewerAssignedMail;
use App\Mail\InterviewScheduledMail;
use App\Models\Application;
use App\Models\Interview;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InterviewController extends ApiController
{
    protected function authorizeView(Request $request, Interview $interview): void
    {
        $user = $request->user();

        abort_unless($user->isStaff() || $interview->interviewer_id === $user->id, 403, 'Kamu tidak punya akses ke interview ini.');
    }

    /** Staff melihat semua; role lain hanya interview miliknya. Filter: status, interviewer_id, upcoming=1, from, to */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Interview::with(['application.candidate', 'application.jobPosting', 'interviewer']);

        if (! $user->isStaff()) {
            $query->where('interviewer_id', $user->id);
        } elseif ($request->filled('interviewer_id')) {
            $query->where('interviewer_id', (int) $request->query('interviewer_id'));
        }

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->boolean('upcoming'), fn ($q) => $q->where('status', 'scheduled')->where('scheduled_at', '>=', now()))
            ->when($request->filled('from'), fn ($q) => $q->where('scheduled_at', '>=', Carbon::parse($request->query('from'))->startOfDay()))
            ->when($request->filled('to'), fn ($q) => $q->where('scheduled_at', '<=', Carbon::parse($request->query('to'))->endOfDay()))
            ->orderBy('scheduled_at');

        return InterviewResource::collection($query->paginate($this->perPage($request))->withQueryString());
    }

    public function show(Request $request, Interview $interview)
    {
        $this->authorizeView($request, $interview);

        return new InterviewResource($interview->load(['application.candidate', 'application.jobPosting', 'interviewer']));
    }

    /** POST /applications/{id}/interviews */
    public function store(Request $request, Application $application)
    {
        $data = $request->validate([
            'interviewer_id' => 'required|exists:users,id',
            'type' => 'required|in:phone,video,onsite',
            'scheduled_at' => 'required|date|after:now',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'location_or_link' => 'nullable|string|max:255',
        ]);

        $start = Carbon::parse($data['scheduled_at']);
        $end = $start->copy()->addMinutes((int) $data['duration_minutes']);

        $clash = Interview::where('interviewer_id', $data['interviewer_id'])
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$start->copy()->subHours(5), $end])
            ->get()
            ->first(fn ($i) => $i->scheduled_at < $end && $i->scheduled_at->copy()->addMinutes($i->duration_minutes) > $start);

        if ($clash) {
            throw ValidationException::withMessages([
                'scheduled_at' => ['Interviewer sudah punya jadwal yang bertabrakan pada ' . $clash->scheduled_at->format('d M Y H:i') . '.'],
            ]);
        }

        $interview = Interview::create($data + ['application_id' => $application->id]);

        $application->moveToStage('interview', $request->user()->id, 'Interview dijadwalkan.');

        try {
            $application->loadMissing('candidate');
            Mail::to($application->candidate->email)->send(new InterviewScheduledMail($interview));
            Mail::to($interview->interviewer->email)->send(new InterviewerAssignedMail($interview));
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email interview', ['interview_id' => $interview->id, 'error' => $e->getMessage()]);
        }

        return (new InterviewResource($interview->load(['application.candidate', 'interviewer'])))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PATCH /interviews/{id}
     * Recruiter/admin: semua field. Interviewer pemilik: hanya status, feedback, rating.
     */
    public function update(Request $request, Interview $interview)
    {
        $user = $request->user();
        $canManage = $user->isRecruiter();

        abort_unless($canManage || $interview->interviewer_id === $user->id, 403, 'Kamu tidak punya akses ke interview ini.');

        $rules = [
            'status' => ['sometimes', Rule::in($canManage ? ['scheduled', 'completed', 'cancelled', 'no_show'] : ['completed', 'cancelled', 'no_show'])],
            'feedback' => 'sometimes|nullable|string|max:5000',
            'rating' => 'sometimes|nullable|integer|min:1|max:5',
        ];

        if ($canManage) {
            $rules += [
                'interviewer_id' => 'sometimes|exists:users,id',
                'type' => 'sometimes|in:phone,video,onsite',
                'scheduled_at' => 'sometimes|date',
                'duration_minutes' => 'sometimes|integer|min:15|max:240',
                'location_or_link' => 'sometimes|nullable|string|max:255',
            ];
        }

        $interview->update($request->validate($rules));

        return new InterviewResource($interview->load(['application.candidate', 'interviewer']));
    }

    public function destroy(Interview $interview)
    {
        $interview->delete();

        return response()->noContent();
    }
}
