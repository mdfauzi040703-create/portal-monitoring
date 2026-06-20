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

Route::get('/make-storage-link', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        $output = \Illuminate\Support\Facades\Artisan::output();
        return '<pre>' . $output . '</pre>';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

Route::get('/check-file/{filename}', function ($filename) {
    $path = storage_path('app/public/documents/' . $filename);
    $exists = file_exists($path);
    $size   = $exists ? filesize($path) : 0;

    // List semua file yang ada di folder documents
    $allFiles = glob(storage_path('app/public/documents/*'));

    return "Cek file: {$filename}<br>"
        . "Path: {$path}<br>"
        . "Exists: " . ($exists ? 'YA' : 'TIDAK') . "<br>"
        . "Size: {$size} bytes<br><br>"
        . "Total file di folder documents: " . count($allFiles) . "<br>"
        . "Daftar file:<br>" . implode('<br>', array_map('basename', $allFiles));
});
// ============================================
// CATCH-ALL HARUS SELALU DI PALING BAWAH!
// ============================================
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');