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
Route::get('/debug-notif', function() {
    $today = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
    
    $docs = \App\Models\Document::whereNull('return_actual_date')
        ->get(['nomor_dokumen','review_deadline','status'])
        ->map(function($d) use ($today) {
            $diff = (int) round($today->diffInDays(
                \Carbon\Carbon::parse($d->review_deadline)->startOfDay(), false
            ));
            return [
                'nomor' => $d->nomor_dokumen,
                'deadline' => $d->review_deadline,
                'diff' => $diff,
                'status' => $d->status,
            ];
        });

    $logs = \App\Models\NotificationLog::latest()->take(10)
        ->get(['document_id','notif_type','status','sent_at']);

    return response()->json([
        'server_time' => $today->toString(),
        'documents' => $docs,
        'logs' => $logs,
    ], 200, [], JSON_PRETTY_PRINT);
})->withoutMiddleware(['auth:sanctum', 'auth']);