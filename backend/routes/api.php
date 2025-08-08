<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SurveyController;
use App\Http\Controllers\Api\SurveyResponseController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// All routes are now public - no authentication required

// Survey routes
Route::prefix('surveys')->group(function () {
    Route::get('/', [SurveyController::class, 'index']);
    Route::get('/{survey}', [SurveyController::class, 'show']);
    Route::post('/', [SurveyController::class, 'store']);
    Route::put('/{survey}', [SurveyController::class, 'update']);
    Route::delete('/{survey}', [SurveyController::class, 'destroy']);
    Route::post('/{survey}/questions', [SurveyController::class, 'addQuestion']);
    Route::put('/{survey}/questions/{question}', [SurveyController::class, 'updateQuestion']);
    Route::delete('/{survey}/questions/{question}', [SurveyController::class, 'deleteQuestion']);
    Route::post('/{survey}/activate', [SurveyController::class, 'activate']);
    Route::post('/{survey}/pause', [SurveyController::class, 'pause']);
    Route::post('/{survey}/close', [SurveyController::class, 'close']);
    Route::get('/{survey}/statistics', [SurveyController::class, 'statistics']);
});

// Survey response routes
Route::prefix('survey-responses')->group(function () {
    Route::get('/', [SurveyResponseController::class, 'index']);
    Route::get('/{surveyResponse}', [SurveyResponseController::class, 'show']);
    Route::post('/', [SurveyResponseController::class, 'store']);
    Route::get('/by-survey/{survey}', [SurveyResponseController::class, 'bySurvey']);
});

// Dashboard routes
Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/survey-stats/{survey}', [DashboardController::class, 'surveyStats']);
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity']);
});

// Public survey access (for anonymous responses)
Route::prefix('public')->group(function () {
    Route::get('/surveys/{accessCode}', [SurveyController::class, 'publicShow']);
    Route::post('/surveys/{accessCode}/responses', [SurveyResponseController::class, 'publicStore']);
}); 