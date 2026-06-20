<?php
use Illuminate\Support\Facades\Route;

// Serve file storage langsung lewat Laravel (karena pakai php artisan serve, bukan nginx)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*');

Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    if ($secret !== env('CRON_SECRET', 'ganti-rahasia-ini')) {
        abort(403, 'Unauthorized');
    }
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    $sentCount = substr_count($output, 'terkirim ke');
    return "OK. {$sentCount} notifikasi diproses pada " . now()->format('d M Y H:i');
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');