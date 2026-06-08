<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$docs = App\Models\Document::whereNotNull('file_path')->get();

if ($docs->isEmpty()) {
    echo "Tidak ada dokumen dengan file!\n";
} else {
    foreach ($docs as $doc) {
        $path   = storage_path('app/public/' . $doc->file_path);
        $exists = file_exists($path) ? 'ADA' : 'TIDAK ADA';
        echo "ID: {$doc->id} | {$doc->nomor_dokumen}\n";
        echo "  file_path : {$doc->file_path}\n";
        echo "  File disk : {$exists}\n";
        echo "  URL akses : http://localhost:8000/storage/{$doc->file_path}\n\n";
    }
}