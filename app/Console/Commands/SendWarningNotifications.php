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

            $message = "[{$label}] Reminder Dokumen\n"
                . "Nomor   : {$doc->nomor_dokumen}\n"
                . "Project : {$doc->project->name}\n"
                . "Deadline: " . Carbon::parse($doc->review_deadline)->format('d M Y') . "\n"
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
                $this->info("Email terkirim ke {$doc->pic->email}");
            } catch (\Exception $e) {
                $this->error("Email gagal: " . $e->getMessage());
            }

            // Kirim WhatsApp via Meta Cloud API
            if ($doc->pic && $doc->pic->whatsapp) {
                $waSent = $this->sendWhatsApp($doc->pic->whatsapp, $message);
            }

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
                'channel'      => 'both',
                'status'       => ($emailSent || $waSent) ? 'sent' : 'failed',
                'message_body' => $message,
                'sent_at'      => now('Asia/Jakarta'),
            ]);

            $this->info("Notifikasi [{$label}] terkirim ke {$doc->pic->name} — {$doc->nomor_dokumen}");
        }

        $this->info('Selesai cek semua dokumen.');
        return 0;
    }

    private function sendWhatsApp(string $phone, string $message): bool
    {
        // Format nomor: hilangkan 0 di depan, tambah 62
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        try {
$response = Http::withToken(env('WHATSAPP_TOKEN'))
    ->post('https://graph.facebook.com/v25.0/' . env('WHATSAPP_PHONE_NUMBER_ID') . '/messages', [
        'messaging_product' => 'whatsapp',
        'to'                => $phone,
        'type'              => 'template',
        'template'          => [
            'name'     => 'hello_world',
            'language' => ['code' => 'en_US'],
        ],
    ]);

            if ($response->successful()) {
                $this->info("WA terkirim ke {$phone}");
                return true;
            } else {
                $this->error("WA gagal ke {$phone}: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            $this->error("WA error: " . $e->getMessage());
            return false;
        }
    }
}