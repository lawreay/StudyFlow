<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttemptController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SubjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'data' => ['status' => 'ok'],
        'message' => 'API is running',
    ]);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{subject}', [SubjectController::class, 'show']);

    Route::get('/questions', [QuestionController::class, 'index']);
    Route::get('/questions/{question}', [QuestionController::class, 'show']);

    Route::post('/attempts', [AttemptController::class, 'store']);
    Route::get('/attempts/{attempt}', [AttemptController::class, 'show']);
    Route::post('/attempts/{attempt}/answers', [AttemptController::class, 'submitAnswer']);

    Route::get('/progress', [ProgressController::class, 'index']);
});
