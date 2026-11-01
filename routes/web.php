<?php

use App\Http\Controllers\ApplicationPipelineController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CandidateApplicationController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// --- Career page publik (tanpa login) ---
Route::get('/karir', [CandidateApplicationController::class, 'careerPage'])->name('career.index');
Route::get('/karir/{job}/lamar', [CandidateApplicationController::class, 'showApplyForm'])->name('career.apply');
Route::post('/karir/{job}/lamar', [CandidateApplicationController::class, 'submit'])->name('career.submit');

// --- Kandidat mengerjakan tes online via link unik (tanpa login) ---
Route::get('/tes/{candidateAssessment}', [AssessmentController::class, 'take'])->name('assessments.take');
Route::post('/tes/{candidateAssessment}', [AssessmentController::class, 'submit'])->name('assessments.submit');

// --- Area internal (recruiter/admin/hiring manager, wajib login) ---
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');

    Route::resource('jobs', JobPostingController::class);
Route::patch('/jobs/{job}/status', [JobPostingController::class, 'updateStatus'])->name('jobs.status');
    Route::get('/jobs/{job}/pipeline', [ApplicationPipelineController::class, 'board'])->name('pipeline.board');
    Route::get('/applications/{application}', [ApplicationPipelineController::class, 'show'])->name('pipeline.show');
    Route::patch('/applications/{application}/stage', [ApplicationPipelineController::class, 'moveStage'])->name('pipeline.move-stage');
    Route::post('/applications/{application}/rescreen', [ApplicationPipelineController::class, 'rescreen'])->name('pipeline.rescreen');
Route::get('/applications/{application}/ai-status', [ApplicationPipelineController::class, 'aiStatus'])->name('pipeline.ai-status');

    Route::get('/applications/{application}/interview/create', [InterviewController::class, 'create'])->name('interviews.create');
    Route::post('/applications/{application}/interview', [InterviewController::class, 'store'])->name('interviews.store');
    Route::patch('/interviews/{interview}/feedback', [InterviewController::class, 'submitFeedback'])->name('interviews.feedback');

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

    Route::middleware('admin')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show']);
    });
});

// --- Set password untuk user yang baru diundang (tanpa login) ---
Route::get('/invite/{token}', [UserManagementController::class, 'showSetPassword'])->name('invite.set-password');
Route::post('/invite/{token}', [UserManagementController::class, 'setPassword'])->name('invite.store-password');

require __DIR__.'/auth.php';
