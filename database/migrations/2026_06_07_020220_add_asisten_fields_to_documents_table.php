<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            // Siapa yang input proyek (asisten manager)
            $table->foreignId('asisten_id')->nullable()->constrained('users')->onDelete('set null')->after('atasan_id');
            // Status pengiriman ke manager
            $table->enum('submit_status', ['draft','submitted','assigned','selesai'])->default('draft')->after('status');
            // Catatan dari asisten ke manager
            $table->text('catatan')->nullable()->after('submit_status');
        });
    }
    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['asisten_id']);
            $table->dropColumn(['asisten_id','submit_status','catatan']);
        });
    }
};