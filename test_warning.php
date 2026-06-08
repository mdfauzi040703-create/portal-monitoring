<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$docs = App\Models\Document::whereNull('return_actual_date')->get();

if ($docs->isEmpty()) {
    echo "Tidak ada dokumen. Buat dokumen dulu di portal!\n";
    exit;
}

// Ambil 3 dokumen pertama untuk di-test
$today    = \Carbon\Carbon::today();
$statuses = [
    0 => ['deadline' => $today->copy()->addDay(),    'label' => 'H-1 (Early Warning)'],
    1 => ['deadline' => $today->copy(),              'label' => 'Hari H (Alert)'],
    2 => ['deadline' => $today->copy()->subDay(),    'label' => 'H+1 (Alert)'],
];

foreach ($statuses as $i => $s) {
    if (!isset($docs[$i])) continue;
    $doc = $docs[$i];
    $doc->review_deadline = $s['deadline']->format('Y-m-d');
    $doc->save();
    echo "Dokumen [{$doc->nomor_dokumen}] → deadline diset ke {$s['label']}\n";
}

echo "\nSekarang jalankan: php artisan notify:warnings\n";