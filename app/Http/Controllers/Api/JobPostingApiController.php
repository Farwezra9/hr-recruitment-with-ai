<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JobPostingApiController extends Controller
{
    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return [
            'title' => [$required, 'string', 'max:255'],
            'department_id' => ['sometimes', 'nullable', 'exists:departments,id'],
            'description' => [$required, 'string'],
            'requirements' => [$required, 'string'],
            'responsibilities' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employment_type' => [$required, 'in:full_time,part_time,contract,internship'],
            'work_mode' => [$required, 'in:onsite,remote,hybrid'],
            'salary_min' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'salary_max' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'vacancies' => [$required, 'integer', 'min:1'],
            'status' => [$required, 'in:draft,open,closed,archived'],
            'closing_date' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'sometimes|nullable|in:draft,open,closed,archived',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);
        $query = JobPosting::with('department')->withCount('applications');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return response()->json($query->latest()->paginate($filters['per_page'] ?? 15));
    }

    public function show(JobPosting $job)
    {
        $job->load('department');

        return response()->json($job);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $data['created_by'] = $request->user()->id;
        $job = JobPosting::create($data);

        return response()->json($job->load('department'), 201);
    }

    public function update(Request $request, JobPosting $job)
    {
        $validator = Validator::make($request->all(), $this->rules(updating: true));
        $validator->after(function ($validator) use ($request, $job) {
            $salaryMin = $request->input('salary_min', $job->salary_min);
            $salaryMax = $request->input('salary_max', $job->salary_max);

            if ($salaryMin !== null && $salaryMax !== null && (int) $salaryMax < (int) $salaryMin) {
                $validator->errors()->add('salary_max', 'Gaji maksimum tidak boleh lebih rendah dari gaji minimum.');
            }
        });

        $job->update($validator->validate());

        return response()->json($job->fresh()->load('department'));
    }

    public function destroy(JobPosting $job)
    {
        $job->update(['status' => 'closed']);
        $job->delete();

        return response()->noContent();
    }
}
