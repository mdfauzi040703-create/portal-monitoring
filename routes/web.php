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

Route::get('/storage/{folder}/{filename}', function ($folder, $filename) {
    $fullPath = storage_path("app/public/{$folder}/{$filename}");
    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan di: ' . $fullPath);
    }
    return response()->file($fullPath);
});

// FIX: pakai afterResponse() supaya tidak timeout di cron-job.org
Route::get('/cron/notify-warnings/{secret}', function ($secret) {
    if ($secret !== env('CRON_SECRET', 'ganti-rahasia-ini')) {
        abort(403, 'Unauthorized');
    }

    // Catat waktu mulai
    $startTime = now('Asia/Jakarta')->format('d M Y H:i:s');

    // Jalankan command SETELAH response dikirim ke cron-job.org
    app()->terminating(function () {
        \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    });

    return response()->json([
        'status'  => 'ok',
        'message' => 'Notifikasi dijadwalkan untuk dijalankan',
        'time'    => $startTime,
    ]);
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

Route::get('/check-logs', function () {
    $logs = \App\Models\NotificationLog::orderBy('id', 'desc')->take(10)->get();
    $output = '';
    foreach ($logs as $log) {
        $output .= "ID:{$log->id} doc:{$log->document_id} type:{$log->notif_type} status:{$log->status} sent_at:{$log->sent_at}<br>";
    }
    return $output ?: 'Tidak ada log sama sekali';
});
Route::get('/check-logs-doc/{nama}', function ($nama) {
    $doc = \App\Models\Document::where('nomor_dokumen', $nama)->first();
    if (!$doc) return 'Dokumen tidak ditemukan';
    
    $logs = \App\Models\NotificationLog::where('document_id', $doc->id)
        ->orderBy('id','desc')->get();
    
    $output = "Dokumen: {$doc->nomor_dokumen} | ID: {$doc->id} | PIC ID: {$doc->pic_id}<br><br>";
    foreach ($logs as $log) {
        $output .= "type:{$log->notif_type} | status:{$log->status} | sent_at:{$log->sent_at}<br>";
    }
    return $output ?: 'Tidak ada log';
});

Route::get('/test-deadline', function () {
    $today = \Carbon\Carbon::today();
    $docs  = \App\Models\Document::whereNotNull('pic_id')->whereNull('return_actual_date')->take(3)->get();

    if ($docs->isEmpty()) {
        return 'Tidak ada dokumen yang sudah di-assign ke PIC!';
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
    return "Berhasil hapus {$count} log notifikasi hari ini.";
});

Route::get('/clear-all-logs', function () {
    $count = \App\Models\NotificationLog::where('id', '>=', 1)->delete();
    return "Berhasil hapus {$count} log notifikasi. Sekarang coba jalankan cron lagi.";
});

Route::get('/debug-diff', function () {
    $doc    = \App\Models\Document::find(38);
    $today  = \Carbon\Carbon::now('Asia/Jakarta')->startOfDay();
    $deadline = \Carbon\Carbon::parse($doc->review_deadline)->startOfDay();
    $diff   = (int) round($today->diffInDays($deadline, false));
    
    return response()->json([
        'server_now'   => now()->toString(),
        'jakarta_now'  => $today->toString(),
        'deadline'     => $deadline->toString(),
        'diff'         => $diff,
        'return_actual'=> $doc->return_actual_date,
        'pic_id'       => $doc->pic_id,
    ]);
});

Route::get('/fix-doc-38', function () {
    $doc = \App\Models\Document::find(38);
    $doc->return_actual_date = null;
    $doc->submit_status = 'assigned';
    $doc->status = 'pending';
    $doc->save();
    
    return response()->json([
        'return_actual' => $doc->return_actual_date,
        'status'        => $doc->status,
        'submit_status' => $doc->submit_status,
    ]);
});

Route::get('/check-pic/{id}', function ($id) {
    $user = \App\Models\User::find($id);
    if (!$user) return 'User tidak ditemukan';
    return response()->json([
        'id'        => $user->id,
        'name'      => $user->name,
        'whatsapp'  => $user->whatsapp,
        'email'     => $user->email,
    ]);
});

Route::get('/update-pic-wa', function () {
    $user = \App\Models\User::find(14);
    $user->whatsapp = '6282386462863';
    $user->save();
    return 'WA updated: ' . $user->whatsapp;
});
Route::get('/test-wa', function () {
    $phone = '6282386462863';
    $message = "Test notifikasi Portal Monitoring";
    
    $response = \Illuminate\Support\Facades\Http::withToken(env('WHATSAPP_TOKEN'))
        ->post('https://graph.facebook.com/v25.0/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages', [
            'messaging_product' => 'whatsapp',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => ['body' => $message],
        ]);

    return response()->json([
        'status'   => $response->status(),
        'response' => $response->json(),
        'token'    => substr(env('WHATSAPP_TOKEN'), 0, 20) . '...',
        'phone_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ]);
});
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');