<?php
use Illuminate\Support\Facades\Route;

Route::get('/check-migrations', function () {
    $migrations = \Illuminate\Support\Facades\DB::table('migrations')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->pluck('migration');
    return '<pre>' . implode("\n", $migrations->toArray()) . '</pre>';
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

Route::get('/force-migrate', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return '<pre>' . $output . '</pre>';
    } catch (\Exception $e) {
        return 'ERROR: ' . $e->getMessage();
    }
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');