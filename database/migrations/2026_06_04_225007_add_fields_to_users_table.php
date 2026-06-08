<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp')->nullable()->after('email');
            $table->enum('role', ['admin','pic','atasan'])->default('pic')->after('whatsapp');
            $table->foreignId('atasan_id')->nullable()->constrained('users')->onDelete('set null')->after('role');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp','role','atasan_id']);
        });
    }
};