<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Untuk MySQL — ubah enum kolom status di tabel documents
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending','early_warning','alert','overdue','selesai') NOT NULL DEFAULT 'pending'");

        // Untuk kolom notif_type di tabel notification_logs
        DB::statement("ALTER TABLE notification_logs MODIFY COLUMN notif_type ENUM('early_warning','alert','overdue') NOT NULL");
    }

    public function down(): void
    {
        // Rollback ke enum lama kalau perlu
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('pending','early_warning','alert','selesai') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE notification_logs MODIFY COLUMN notif_type ENUM('early_warning','alert') NOT NULL");
    }
};