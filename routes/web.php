<?php

use App\Http\Controllers\ApplicationListController;
use App\Http\Controllers\ApplicationPipelineController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CandidateApplicationController;
use App\Http\Controllers\CandidateCvController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Publik (tanpa login)
// ---------------------------------------------------------------
Route::get('/', fn () => redirect()->route('career.index'));

Route::get('/karir', [CandidateApplicationController::class, 'careerPage'])->name('career.index');
Route::get('/karir/{job}/lamar', [CandidateApplicationController::class, 'showApplyForm'])->name('career.apply');
Route::post('/karir/{job}/lamar', [CandidateApplicationController::class, 'submit'])
    ->middleware('throttle:6,1') // maks 6 lamaran per menit per IP
    ->name('career.submit');

// Tes online: akses lewat token acak, bukan ID angka
Route::get('/tes/{candidateAssessment}', [AssessmentController::class, 'take'])->name('assessments.take');
Route::post('/tes/{candidateAssessment}', [AssessmentController::class, 'submit'])
    ->middleware('throttle:10,1')
    ->name('assessments.submit');

// Staff baru mengatur password lewat link undangan
Route::get('/invite/{token}', [UserManagementController::class, 'showSetPassword'])->name('invite.set-password');
Route::post('/invite/{token}', [UserManagementController::class, 'setPassword'])
    ->middleware('throttle:10,1')
    ->name('invite.store-password');

// ---------------------------------------------------------------
// Area internal (wajib login)
// ---------------------------------------------------------------
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Profil & ganti password (semua role)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Interview: semua user yang ditugaskan sebagai interviewer
    Route::get('/my-interviews', [InterviewController::class, 'mine'])->name('interviews.mine');
    Route::patch('/interviews/{interview}/feedback', [InterviewController::class, 'submitFeedback'])->name('interviews.feedback');

    // Unduh CV (otorisasi dicek di dalam controller)
    Route::get('/candidates/{candidate}/cv', CandidateCvController::class)->name('candidates.cv');

    // --- Admin saja ---
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);
        Route::post('/users/{user}/resend-invite', [UserManagementController::class, 'resendInvite'])->name('users.resend-invite');

        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        Route::post('/profile/tokens', [ProfileController::class, 'storeToken'])->name('profile.tokens.store');
        Route::delete('/profile/tokens/{tokenId}', [ProfileController::class, 'destroyToken'])->name('profile.tokens.destroy');
    });

    // --- Recruiter (admin + recruiter): mengubah data rekrutmen ---
    // Didaftarkan SEBELUM grup staff supaya /jobs/create tidak tertangkap oleh /jobs/{job}.
    Route::middleware('role:admin,recruiter')->group(function () {
        Route::get('/jobs/create', [JobPostingController::class, 'create'])->name('jobs.create');
        Route::post('/jobs', [JobPostingController::class, 'store'])->name('jobs.store');
        Route::get('/jobs/trashed', [JobPostingController::class, 'trashed'])->name('jobs.trashed');
        Route::patch('/jobs/{job}/restore', [JobPostingController::class, 'restore'])->withTrashed()->name('jobs.restore');
        Route::get('/jobs/{job}/edit', [JobPostingController::class, 'edit'])->name('jobs.edit');
        Route::put('/jobs/{job}', [JobPostingController::class, 'update'])->name('jobs.update');
        Route::delete('/jobs/{job}', [JobPostingController::class, 'destroy'])->name('jobs.destroy');
        Route::patch('/jobs/{job}/status', [JobPostingController::class, 'updateStatus'])->name('jobs.status');
        Route::post('/jobs/{job}/rescreen-all', [JobPostingController::class, 'rescreenAll'])->name('jobs.rescreen-all');

        Route::patch('/applications/{application}/stage', [ApplicationPipelineController::class, 'moveStage'])->name('pipeline.move-stage');
        Route::post('/applications/{application}/rescreen', [ApplicationPipelineController::class, 'rescreen'])->name('pipeline.rescreen');

        Route::get('/applications/{application}/interview/create', [InterviewController::class, 'create'])->name('interviews.create');
        Route::post('/applications/{application}/interview', [InterviewController::class, 'store'])->name('interviews.store');

        // Bank tes
        Route::get('/assessments', [AssessmentController::class, 'index'])->name('assessments.index');
        Route::get('/assessments/create', [AssessmentController::class, 'create'])->name('assessments.create');
        Route::post('/assessments', [AssessmentController::class, 'store'])->name('assessments.store');
        Route::get('/assessments/{assessment}/edit', [AssessmentController::class, 'edit'])->name('assessments.edit');
        Route::put('/assessments/{assessment}', [AssessmentController::class, 'update'])->name('assessments.update');
        Route::delete('/assessments/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

        Route::post('/assessments/{assessment}/questions', [AssessmentController::class, 'storeQuestion'])->name('assessments.questions.store');
        Route::put('/assessment-questions/{question}', [AssessmentController::class, 'updateQuestion'])->name('assessments.questions.update');
        Route::delete('/assessment-questions/{question}', [AssessmentController::class, 'destroyQuestion'])->name('assessments.questions.destroy');

        Route::post('/applications/{application}/assign-assessment', [AssessmentController::class, 'assignToApplication'])->name('assessments.assign');
        Route::patch('/candidate-assessments/{candidateAssessment}/grade', [AssessmentController::class, 'grade'])->name('assessments.grade');
    });

    // --- Staff (admin, recruiter, hiring manager): hanya melihat ---
    Route::middleware('role:admin,recruiter,hiring_manager')->group(function () {
        Route::get('/jobs', [JobPostingController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{job}', [JobPostingController::class, 'show'])->name('jobs.show');
        Route::get('/jobs/{job}/pipeline', [ApplicationPipelineController::class, 'board'])->name('pipeline.board');

        Route::get('/applications', [ApplicationListController::class, 'index'])->name('applications.index');
        Route::get('/applications/export', [ApplicationListController::class, 'export'])->name('applications.export');
        Route::get('/applications/{application}', [ApplicationPipelineController::class, 'show'])->name('pipeline.show');
        Route::get('/applications/{application}/ai-status', [ApplicationPipelineController::class, 'aiStatus'])->name('pipeline.ai-status');
    });
});

require __DIR__.'/auth.php';
