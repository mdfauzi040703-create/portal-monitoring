<?php
use Illuminate\Support\Facades\Route;

Route::get('/clear-config', function () {
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return 'Config cache cleared! CRON_SECRET sekarang: ' . env('CRON_SECRET', 'TIDAK ADA');
});

Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    $expected = env('CRON_SECRET', 'ganti-rahasia-ini');
    if ($secret !== $expected) {
        return "Secret tidak cocok. Yang dikirim: {$secret} | Yang diharapkan: {$expected}";
    }
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return '<pre>' . $output . '</pre>';
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');