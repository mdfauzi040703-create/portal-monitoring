<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('target_asisten_id')->nullable()->constrained('users')->onDelete('set null')->after('asisten_id');
        });
    }
    public function down(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['target_asisten_id']);
            $table->dropColumn('target_asisten_id');
        });
    }
};