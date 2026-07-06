<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\NotificationLogController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout',        [AuthController::class, 'logout']);
    Route::get('/me',             [AuthController::class, 'me']);
    Route::get('/dashboard',      [DashboardController::class, 'index']);
    Route::apiResource('/projects',          ProjectController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('/notification-logs', NotificationLogController::class);
    Route::apiResource('/users',             UserController::class);
    Route::put('/users/{user}', [UserController::class, 'update']);
});
