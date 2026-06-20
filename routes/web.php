<?php
use Illuminate\Support\Facades\Route;

// ============================================
// SEMUA ROUTE KHUSUS HARUS DI ATAS CATCH-ALL!
// ============================================

Route::get('/check-storage', function () {
    $linkPath = public_path('storage');
    $isLink   = is_link($linkPath);
    $exists   = file_exists($linkPath);
    $target   = $isLink ? readlink($linkPath) : 'BUKAN SYMLINK';

    return "Path: {$linkPath}<br>Is Link: " . ($isLink ? 'YA' : 'TIDAK') . "<br>Exists: " . ($exists ? 'YA' : 'TIDAK') . "<br>Target: {$target}";
});

Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    if ($secret !== env('CRON_SECRET', 'ganti-rahasia-ini')) {
        abort(403, 'Unauthorized');
    }
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    $sentCount = substr_count($output, 'terkirim ke');
    return "OK. {$sentCount} notifikasi diproses pada " . now()->format('d M Y H:i');
});

// ============================================
// CATCH-ALL HARUS SELALU DI PALING BAWAH!
// ============================================
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');