<?php
use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');

Route::get('/seed-users', function () {
    if (\App\Models\User::where('email', 'md.fauzi040703@gmail.com')->exists()) {
        return 'User sudah ada!';
    }

    \App\Models\User::create(['name'=>'Admin','email'=>'admin@gmail.com','password'=>bcrypt('bacot123'),'role'=>'admin','whatsapp'=>'6282368462863']);
    \App\Models\User::create(['name'=>'Asisten Manager','email'=>'md.fauzi040703@gmail.com','password'=>bcrypt('bacot123'),'role'=>'asisten_manager','whatsapp'=>'6285180009913']);
    \App\Models\User::create(['name'=>'Manager','email'=>'Zzz471067@gmail.com','password'=>bcrypt('bacot123'),'role'=>'manager','whatsapp'=>'6282187306681']);
    \App\Models\User::create(['name'=>'PIC Test','email'=>'md.fauzi531@gmail.com','password'=>bcrypt('bacot123'),'role'=>'pic','whatsapp'=>'6282386462863']);

    return 'Berhasil membuat 4 user! Asisten Manager: md.fauzi040703@gmail.com / bacot123';
});