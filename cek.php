<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('email', 'admin@portal.com')->first();

if ($user) {
    echo "User ditemukan!\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Password hash: " . $user->password . "\n";
    
    // Test password
    $cek = \Illuminate\Support\Facades\Hash::check('admin123', $user->password);
    echo "Password admin123 cocok: " . ($cek ? "YA" : "TIDAK") . "\n";
} else {
    echo "User tidak ditemukan!\n";
}