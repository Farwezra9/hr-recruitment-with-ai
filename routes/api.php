<?php

use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API v1  (prefix otomatis: /api/v1)
|--------------------------------------------------------------------------
| Auth: Bearer token (Laravel Sanctum). Ambil token lewat POST /api/v1/auth/login.
| Dokumentasi lengkap: docs/API.md
*/
Route::prefix('v1')->name('api.v1.')->group(function () {

    // ---- Publik ----
    Route::post('auth/login', [V1\AuthController::class, 'login'])->middleware('throttle:20,1')->name('auth.login');

    Route::prefix('public')->name('public.')->group(function () {
        Route::get('jobs', [V1\PublicJobController::class, 'index'])->name('jobs.index');
        Route::get('jobs/{job}', [V1\PublicJobController::class, 'show'])->name('jobs.show');
        Route::post('jobs/{job}/applications', [V1\PublicJobController::class, 'apply'])
            ->middleware('throttle:6,1')
            ->name('jobs.apply');
    });

    // ---- Terautentikasi ----
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

        // Semua role aktif (data dibatasi di dalam controller)
        Route::middleware('role:admin,recruiter,hiring_manager,interviewer')->group(function () {
            Route::get('auth/me', [V1\AuthController::class, 'me'])->name('auth.me');
            Route::post('auth/logout', [V1\AuthController::class, 'logout'])->name('auth.logout');

            Route::get('interviews', [V1\InterviewController::class, 'index'])->name('interviews.index');
            Route::get('interviews/{interview}', [V1\InterviewController::class, 'show'])->name('interviews.show');
            Route::match(['put', 'patch'], 'interviews/{interview}', [V1\InterviewController::class, 'update'])->name('interviews.update');

            Route::get('candidates/{candidate}/cv', [V1\CandidateController::class, 'cv'])->name('candidates.cv');
        });

        // Admin saja
        Route::middleware('role:admin')->group(function () {
            Route::apiResource('users', V1\UserController::class);
        });

        // Admin + recruiter: mengubah data
        Route::middleware('role:admin,recruiter')->group(function () {
            Route::apiResource('jobs', V1\JobPostingController::class)->only(['store', 'update', 'destroy']);
            Route::post('jobs/{id}/restore', [V1\JobPostingController::class, 'restore'])->whereNumber('id')->name('jobs.restore');
            Route::post('jobs/{job}/rescreen', [V1\JobPostingController::class, 'rescreen'])->name('jobs.rescreen');

            Route::patch('applications/{application}', [V1\ApplicationController::class, 'update'])->name('applications.update');
            Route::post('applications/{application}/screening', [V1\ApplicationController::class, 'screen'])->name('applications.screening.start');

            Route::post('applications/{application}/interviews', [V1\InterviewController::class, 'store'])->name('interviews.store');
            Route::delete('interviews/{interview}', [V1\InterviewController::class, 'destroy'])->name('interviews.destroy');

            Route::apiResource('assessments', V1\AssessmentController::class)->only(['store', 'update', 'destroy']);
            Route::post('assessments/{assessment}/questions', [V1\AssessmentController::class, 'storeQuestion'])->name('questions.store');
            Route::match(['put', 'patch'], 'questions/{question}', [V1\AssessmentController::class, 'updateQuestion'])->name('questions.update');
            Route::delete('questions/{question}', [V1\AssessmentController::class, 'destroyQuestion'])->name('questions.destroy');

            Route::post('applications/{application}/assessments', [V1\CandidateAssessmentController::class, 'store'])->name('candidate-assessments.store');
            Route::patch('candidate-assessments/{candidateAssessment}/grade', [V1\CandidateAssessmentController::class, 'grade'])->name('candidate-assessments.grade');
        });

        // Admin + recruiter + hiring manager: membaca
        Route::middleware('role:admin,recruiter,hiring_manager')->group(function () {
            Route::apiResource('jobs', V1\JobPostingController::class)->only(['index', 'show']);
            Route::get('jobs/{job}/applications', [V1\ApplicationController::class, 'forJob'])->name('jobs.applications');

            Route::get('applications', [V1\ApplicationController::class, 'index'])->name('applications.index');
            Route::get('applications/{application}', [V1\ApplicationController::class, 'show'])->name('applications.show');
            Route::get('applications/{application}/screening', [V1\ApplicationController::class, 'screeningStatus'])->name('applications.screening.status');
            Route::get('applications/{application}/assessments', [V1\CandidateAssessmentController::class, 'index'])->name('candidate-assessments.index');

            Route::get('candidates', [V1\CandidateController::class, 'index'])->name('candidates.index');
            Route::get('candidates/{candidate}', [V1\CandidateController::class, 'show'])->name('candidates.show');

            Route::apiResource('assessments', V1\AssessmentController::class)->only(['index', 'show']);
            Route::get('candidate-assessments/{candidateAssessment}', [V1\CandidateAssessmentController::class, 'show'])->name('candidate-assessments.show');

            Route::get('departments', [V1\MetaController::class, 'departments'])->name('departments.index');
            Route::get('stats', [V1\MetaController::class, 'stats'])->name('stats');
        });
    });
});
