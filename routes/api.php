<?php

use App\Http\Controllers\Api\ApplicationApiController;
use App\Http\Controllers\Api\JobPostingApiController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (\Illuminate\Http\Request $request) => $request->user());

    Route::apiResource('jobs', JobPostingApiController::class)->only(['index', 'show', 'store']);

    Route::get('/jobs/{job}/applications', [ApplicationApiController::class, 'index']);
    Route::get('/applications/{application}', [ApplicationApiController::class, 'show']);
    Route::post('/applications/{application}/screen', [ApplicationApiController::class, 'screen']);
    Route::patch('/applications/{application}/stage', [ApplicationApiController::class, 'moveStage']);
});
