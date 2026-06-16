<?php
use Illuminate\Support\Facades\Route;

Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    if ($secret !== env('CRON_SECRET', 'ganti-rahasia-ini')) {
        abort(403, 'Unauthorized');
    }
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return '<pre>' . $output . '</pre>';
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    $expected = env('CRON_SECRET', 'ganti-rahasia-ini');
    if ($secret !== $expected) {
        return "Secret tidak cocok. Yang dikirim: {$secret} | Yang diharapkan: {$expected}";
    }
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return '<pre>' . $output . '</pre>';
});