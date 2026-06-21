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
Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $fullPath = storage_path("app/public/{$folder}/{$filename}");

    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan di: ' . $fullPath);
    }

    return response()->file($fullPath);
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

Route::get('/test-email', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('Ini email test dari Portal Monitoring.', function ($mail) {
            $mail->to(env('MAIL_USERNAME'))
                 ->subject('Test Email Portal Monitoring');
        });
        return 'Email berhasil dikirim! Cek inbox ' . env('MAIL_USERNAME');
    } catch (\Exception $e) {
        return 'GAGAL: ' . $e->getMessage();
    }
});

Route::get('/test-deadline', function () {
    $today = \Carbon\Carbon::today();
    $docs  = \App\Models\Document::whereNotNull('pic_id')->whereNull('return_actual_date')->take(3)->get();

    if ($docs->isEmpty()) {
        return 'Tidak ada dokumen yang sudah di-assign ke PIC! Assign dulu proyek ke PIC dari Asisten Manager.';
    }

    $labels = [];
    foreach ($docs as $i => $doc) {
        $deadline = match($i) {
            0 => $today->copy()->addDay(),
            1 => $today->copy(),
            2 => $today->copy()->subDay(),
            default => $today,
        };
        $doc->review_deadline = $deadline->format('Y-m-d');
        $doc->updateStatus();
        $doc->save();
        $labels[] = "{$doc->nomor_dokumen} -> deadline {$deadline->format('d M Y')} -> status {$doc->status} -> PIC: {$doc->pic->email}";
    }

    return implode('<br>', $labels);
});

Route::get('/clear-today-logs', function () {
    $count = \App\Models\NotificationLog::whereDate('sent_at', \Carbon\Carbon::today())->delete();
    return "Berhasil hapus {$count} log notifikasi hari ini. Sekarang coba jalankan /cron/notify-warnings lagi.";
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');