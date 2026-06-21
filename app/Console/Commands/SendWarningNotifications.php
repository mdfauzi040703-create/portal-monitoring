<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendWarningNotifications extends Command {
    protected $signature   = 'notify:warnings';
    protected $description = 'Kirim early warning dan alert ke PIC';

public function handle() {
    $today = Carbon::today();

    $documents = Document::with(['pic','project'])
        ->whereNull('return_actual_date')
        ->get();

    $this->info("Total dokumen belum selesai: " . $documents->count());

    foreach ($documents as $doc) {
        if (!$doc->review_deadline || !$doc->pic_id) continue;

        $deadline = Carbon::parse($doc->review_deadline)->startOfDay();
        $diff = (int) round($today->diffInDays($deadline, false));

        $this->info("Dokumen {$doc->nomor_dokumen}: deadline={$doc->review_deadline}, diff={$diff}");

        // diff: +1 = H-1, 0 = hari H, -1 = H+1
        if (!in_array($diff, [1, 0, -1])) continue;

            $type  = $diff === 1 ? 'early_warning' : 'alert';
            $label = match($diff) {
                1  => 'H-1',
                0  => 'Hari H',
                -1 => 'H+1',
            };

            // Cek apakah notif hari ini sudah dikirim
            $alreadySent = NotificationLog::where('document_id', $doc->id)
                ->where('notif_type', $type)
                ->whereDate('sent_at', $today)
                ->exists();

            if ($alreadySent) continue;

$message = "[{$label}] Reminder Dokumen\n"
    . "Nomor   : {$doc->nomor_dokumen}\n"
    . "Project : {$doc->project->name}\n"
    . "Deadline: {$doc->review_deadline->format('d M Y')}\n"
    . "Mohon segera input Return Actual Date.";

            $emailSent = false;
            $waSent    = false;

            // Kirim Email
            try {
                Mail::raw($message, function ($mail) use ($doc, $label) {
                    $mail->to($doc->pic->email)
                         ->subject("[{$label}] Reminder Dokumen {$doc->nomor_dokumen}");
                });
                $emailSent = true;
            } catch (\Exception $e) {
                $this->error("Email gagal: " . $e->getMessage());
            }

            // Kirim WhatsApp via Fonnte
// Kirim WhatsApp via Fonnte
if ($doc->pic->whatsapp) {
    try {
        $token = env('FONNTE_TOKEN');
        $this->info("Token Fonnte: " . ($token ? substr($token, 0, 5) . '...' : 'KOSONG/NULL'));

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/send', [
            'target'  => $doc->pic->whatsapp,
            'message' => $message,
        ]);

        $this->info("Status: " . $response->status());
        $this->info("Body: " . $response->body());

        $waSent = $response->successful();
    } catch (\Exception $e) {
        $this->error("WA gagal: " . $e->getMessage());
    }
}

            // Update status dokumen
            $doc->status = $type;
            $doc->save();

            // Simpan log
            NotificationLog::create([
                'document_id'  => $doc->id,
                'pic_id'       => $doc->pic_id,
                'notif_type'   => $type,
                'channel'      => 'both',
                'status'       => ($emailSent || $waSent) ? 'sent' : 'failed',
                'message_body' => $message,
                'sent_at'      => now(),
            ]);

            $this->info("Notifikasi [{$label}] terkirim ke {$doc->pic->name} — {$doc->nomor_dokumen}");
        }

        $this->info('Selesai cek semua dokumen.');
        return 0;
    }
}