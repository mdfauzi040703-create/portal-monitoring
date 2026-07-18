<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class SendWarningNotifications extends Command {
    protected $signature   = 'notify:warnings';
    protected $description = 'Kirim early warning dan alert ke PIC';

    public function handle() {
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $documents = Document::with(['pic', 'project'])
            ->whereNull('return_actual_date')
            ->whereNotNull('pic_id')
            ->get();

        foreach ($documents as $doc) {
            $deadline = Carbon::parse($doc->review_deadline)->startOfDay();
            $diff = (int) round($today->diffInDays($deadline, false));

            if (!in_array($diff, [1, 0, -1])) continue;

            $type = match($diff) {
                1  => 'early_warning',
                0  => 'alert',
                -1 => 'overdue',
            };

            $label = match($diff) {
                1  => 'H-1',
                0  => 'Hari H',
                -1 => 'H+1',
            };

            $alreadySent = NotificationLog::where('document_id', $doc->id)
                ->where('notif_type', $type)
                ->whereDate('sent_at', $today->toDateString())
                ->exists();

            if ($alreadySent) {
                $this->info("Skip [{$label}] {$doc->nomor_dokumen} — sudah terkirim hari ini.");
                continue;
            }

            $subject = "[{$label}] Reminder Dokumen {$doc->nomor_dokumen}";
            $message = "Halo {$doc->pic->name},\n\n"
                . "Ini adalah reminder untuk dokumen berikut:\n\n"
                . "Nomor   : {$doc->nomor_dokumen}\n"
                . "Project : {$doc->project->name}\n"
                . "Deadline: " . Carbon::parse($doc->review_deadline)->format('d M Y') . "\n\n"
                . "Mohon segera input Return Actual Date sebelum atau pada deadline.\n\n"
                . "Portal: " . env('APP_URL');

            $emailSent = $this->sendEmail($doc->pic->email, $doc->pic->name, $subject, $message);
            $waSent    = false;

            // Update status dokumen
            $doc->status = match($diff) {
                1  => 'early_warning',
                0  => 'alert',
                -1 => 'overdue',
            };
            $doc->save();

            // Simpan log
            NotificationLog::create([
                'document_id'  => $doc->id,
                'pic_id'       => $doc->pic_id,
                'notif_type'   => $type,
                'channel'      => 'email',
                'status'       => $emailSent ? 'sent' : 'failed',
                'message_body' => $message,
                'sent_at'      => now('Asia/Jakarta'),
            ]);

            $this->info("Notifikasi [{$label}] terkirim ke {$doc->pic->name} — {$doc->nomor_dokumen}");
        }

        $this->info('Selesai cek semua dokumen.');
        return 0;
    }

    private function sendEmail(string $to, string $toName, string $subject, string $body): bool
    {
        try {
            $response = Http::withHeaders([
                'accept'       => 'application/json',
                'api-key'      => env('BREVO_API_KEY'),
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender'      => ['name' => env('MAIL_FROM_NAME', 'Portal Monitoring'), 'email' => env('MAIL_FROM_ADDRESS', 'noreply@portalmonitoring.online')],
                'to'          => [['email' => $to, 'name' => $toName]],
                'subject'     => $subject,
                'textContent' => $body,
            ]);

            if ($response->successful()) {
                $this->info("Email terkirim ke {$to}");
                return true;
            } else {
                $this->error("Email gagal ke {$to}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            $this->error("Email error: " . $e->getMessage());
            return false;
        }
    }
}