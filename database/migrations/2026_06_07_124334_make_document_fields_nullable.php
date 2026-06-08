<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->date('review_deadline')->nullable()->change();
            $table->date('tanggal_masuk')->nullable()->change();
        });
    }
    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->date('review_deadline')->nullable(false)->change();
            $table->date('tanggal_masuk')->nullable(false)->change();
        });
    }
};