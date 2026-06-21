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

            if (!in_array($diff, [1, 0, -1])) continue;

            $type  = $diff === 1 ? 'early_warning' : 'alert';
            $label = match($diff) {
                1  => 'H-1',
                0  => 'Hari H',
                -1 => 'H+1',
            };

            $alreadySent = NotificationLog::where('document_id', $doc->id)
                ->where('notif_type', $type)
                ->whereDate('sent_at', $today)
                ->exists();

            if ($alreadySent) {
                $this->info("SKIP {$doc->nomor_dokumen} - sudah pernah dikirim hari ini");
                continue;
            }

            if (!$doc->pic || !$doc->pic->email) {
                $this->error("SKIP {$doc->nomor_dokumen} - PIC tidak punya email");
                continue;
            }

            $message = "[{$label}] Reminder Dokumen\n"
                . "Nomor   : {$doc->nomor_dokumen}\n"
                . "Project : {$doc->project->name}\n"
                . "Deadline: {$doc->review_deadline->format('d M Y')}\n"
                . "Mohon segera input Return Actual Date.";

            $emailSent  = false;
            $emailError = '';
            $waSent     = false;

            // Kirim Email
            try {
                $this->info("Mencoba kirim email ke: {$doc->pic->email}");
                $this->info("MAIL_MAILER: " . config('mail.default'));
                $this->info("MAIL_HOST: " . config('mail.mailers.smtp.host'));
                $this->info("MAIL_FROM: " . config('mail.from.address'));

                Mail::raw($message, function ($mail) use ($doc, $label) {
                    $mail->to($doc->pic->email)
                         ->subject("[{$label}] Reminder Dokumen {$doc->nomor_dokumen}");
                });
                $emailSent = true;
                $this->info("EMAIL BERHASIL terkirim ke {$doc->pic->email}");
            } catch (\Exception $e) {
                $emailError = $e->getMessage();
                $this->error("EMAIL GAGAL: " . $emailError);
            }

            // Kirim WhatsApp via Fonnte
            if ($doc->pic->whatsapp) {
                try {
                    $token = env('FONNTE_TOKEN');
                    $response = Http::withHeaders([
                        'Authorization' => $token,
                    ])->post('https://api.fonnte.com/send', [
                        'target'  => $doc->pic->whatsapp,
                        'message' => $message,
                    ]);
                    $waSent = $response->successful();
                    $this->info("WA Status: " . $response->status() . " | Sukses: " . ($waSent ? 'YA' : 'TIDAK'));
                } catch (\Exception $e) {
                    $this->error("WA GAGAL: " . $e->getMessage());
                }
            }

            $doc->status = $type;
            $doc->save();

            $channel = $emailSent && $waSent ? 'both' : ($emailSent ? 'email' : ($waSent ? 'whatsapp' : 'none'));

            NotificationLog::create([
                'document_id'  => $doc->id,
                'pic_id'       => $doc->pic_id,
                'notif_type'   => $type,
                'channel'      => $channel,
                'status'       => ($emailSent || $waSent) ? 'sent' : 'failed',
                'message_body' => $message,
                'sent_at'      => now(),
            ]);

            $this->info("RINGKASAN [{$label}] {$doc->nomor_dokumen} -> Email: " . ($emailSent ? 'OK' : 'GAGAL') . " ({$emailError}) | WA: " . ($waSent ? 'OK' : 'GAGAL'));
        }

        $this->info('Selesai cek semua dokumen.');
        return 0;
    }
}