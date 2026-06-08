<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
Schema::create('documents', function (Blueprint $table) {
    $table->id();

    $table->string('nomor_dokumen');

    $table->foreignId('project_id')
          ->constrained('projects')
          ->onDelete('cascade');

    $table->foreignId('atasan_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

    $table->foreignId('pic_id')
          ->nullable()
          ->constrained('users')
          ->nullOnDelete();

    $table->date('tanggal_masuk');

    $table->date('review_deadline')
          ->nullable();

    $table->date('return_actual_date')
          ->nullable();

    $table->enum('status', [
        'pending',
        'early_warning',
        'alert',
        'selesai'
    ])->default('pending');

    $table->string('file_path')->nullable();

    $table->timestamps();
});
    }
    public function down(): void {
        Schema::dropIfExists('documents');
    }
};