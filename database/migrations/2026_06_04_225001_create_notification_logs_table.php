<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->onDelete('cascade');
            $table->foreignId('pic_id')->constrained('users')->onDelete('cascade');
            $table->enum('notif_type', ['early_warning','alert']);
            $table->enum('channel', ['email','whatsapp','both'])->default('both');
            $table->enum('status', ['sent','failed'])->default('sent');
            $table->text('message_body')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notification_logs');
    }
};