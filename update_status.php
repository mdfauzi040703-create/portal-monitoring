<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$today = \Carbon\Carbon::today();

$docs = App\Models\Document::whereNull('return_actual_date')->get();

echo "Total dokumen belum selesai: " . $docs->count() . "\n\n";

foreach ($docs as $doc) {
    $deadline = \Carbon\Carbon::parse($doc->review_deadline)->startOfDay();
    $diff     = (int) round($today->diffInDays($deadline, false));

    echo "Dokumen: {$doc->nomor_dokumen}\n";
    echo "  Deadline : {$doc->review_deadline}\n";
    echo "  Hari ini : {$today->format('Y-m-d')}\n";
    echo "  Diff     : {$diff} hari\n";
    echo "  Status lama: {$doc->status}\n";

    $doc->updateStatus();
    $doc->save();

    echo "  Status baru: {$doc->status}\n\n";
}

echo "Selesai!\n";