<?php
use Illuminate\Support\Facades\Route;

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
        $labels[] = "{$doc->nomor_dokumen} -> deadline {$deadline->format('d M Y')} -> status {$doc->status}";
    }

    return implode('<br>', $labels);
});

Route::get('/run-notify', function () {
    \Illuminate\Support\Facades\Artisan::call('notify:warnings');
    $output = \Illuminate\Support\Facades\Artisan::output();
    return '<pre>' . $output . '</pre>';
});

Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');