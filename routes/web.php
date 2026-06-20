<?php
use Illuminate\Support\Facades\Route;

Route::get('/check-file/{filename}', function ($filename) {
    $path = storage_path('app/public/documents/' . $filename);
    $exists = file_exists($path);
    $size   = $exists ? filesize($path) : 0;
    $allFiles = glob(storage_path('app/public/documents/*'));

    return "Cek file: {$filename}<br>"
        . "Path: {$path}<br>"
        . "Exists: " . ($exists ? 'YA' : 'TIDAK') . "<br>"
        . "Size: {$size} bytes<br><br>"
        . "Total file di folder documents: " . count($allFiles) . "<br>"
        . "Daftar file:<br>" . implode('<br>', array_map('basename', $allFiles));
});

// Serve file storage langsung lewat Laravel (karena pakai php artisan serve, bukan nginx)
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan di: ' . $fullPath);
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