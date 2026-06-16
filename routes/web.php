<?php
use Illuminate\Support\Facades\Route;

Route::get('/run-notify', function () {
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    return 'Notifikasi sudah dijalankan! Cek log notifikasi dan email/WA.';
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');