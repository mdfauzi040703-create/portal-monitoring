<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocumentController extends Controller {

    public function index(Request $request) {
        $query = Document::with(['project','pic','atasan','asisten','targetAsisten'])
            ->orderBy('created_at','desc');

        $user = $request->user();
        if ($user && $user->role === 'manager') {
            $query->where('asisten_id', $user->id);
        }
        if ($user && $user->role === 'asisten_manager') {
            $query->where(function($q) use ($user) {
                $q->where('target_asisten_id', $user->id)
                  ->orWhereNull('target_asisten_id');
            });
        }

        if ($request->status)        $query->where('status', $request->status);
        if ($request->project_id)    $query->where('project_id', $request->project_id);
        if ($request->search)        $query->where('nomor_dokumen','like','%'.$request->search.'%');
        if ($request->submit_status) $query->where('submit_status', $request->submit_status);
        if ($request->asisten_id)    $query->where('asisten_id', $request->asisten_id);
        if ($request->atasan_id)     $query->where('atasan_id', $request->atasan_id);
        if ($request->pic_id)        $query->where('pic_id', $request->pic_id);

        return response()->json($query->get());
    }

    public function store(Request $request) {
        $request->validate([
            'nomor_dokumen' => 'required|string',
            'project_id'    => 'required|exists:projects,id',
        ]);

        $data = $request->only(['nomor_dokumen','project_id','catatan','tanggal_masuk','review_deadline']);
        $data['asisten_id']    = $request->user()->id;
        $data['submit_status'] = 'draft';
        $data['status']        = 'pending';
        $data['tanggal_masuk'] = $data['tanggal_masuk'] ?? now()->format('Y-m-d');

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('documents','public');
        }

        $doc = Document::create($data);
        return response()->json($doc->load(['project','pic','atasan','asisten']), 201);
    }

    public function update(Request $request, Document $document) {

        // Manager submit ke Asisten Manager
        if ($request->has('submit_to_manager')) {
            $request->validate([
                'target_asisten_id' => 'required|exists:users,id',
            ]);
            $document->target_asisten_id = $request->target_asisten_id;
            $document->submit_status     = 'submitted';
            $document->save();

            // Kirim email notifikasi ke Asisten Manager
            $asisten = \App\Models\User::find($request->target_asisten_id);
            $manager = $request->user();

            if ($asisten && $asisten->email) {
                try {
                    $subject = "[Dokumen Masuk] {$document->nomor_dokumen} — Perlu Di-assign ke PIC";
                    $body    = "Halo {$asisten->name},\n\n"
                        . "Anda mendapat dokumen baru dari Manager yang perlu di-assign ke PIC.\n\n"
                        . "Detail Dokumen:\n"
                        . "Nomor    : {$document->nomor_dokumen}\n"
                        . "Project  : {$document->project->name}\n"
                        . "Deadline : " . ($document->review_deadline ? Carbon::parse($document->review_deadline)->format('d M Y') : '-') . "\n"
                        . "Dari     : {$manager->name}\n\n"
                        . "Segera buka Portal Monitoring dan assign dokumen ini ke PIC.\n"
                        . "Portal: " . env('APP_URL');

                    Mail::raw($body, function ($mail) use ($asisten, $subject) {
                        $mail->to($asisten->email)->subject($subject);
                    });
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email ke asisten: " . $e->getMessage());
                }
            }

            return response()->json($document->load(['project','pic','atasan','asisten','targetAsisten']));
        }

        // Asisten Manager assign ke PIC
        if ($request->has('assign_to_pic')) {
            $request->validate([
                'pic_id' => 'required|exists:users,id',
            ]);
            $document->pic_id        = $request->pic_id;
            $document->atasan_id     = $request->user()->id;
            $document->submit_status = 'assigned';
            $document->updateStatus();
            $document->save();
            $document->load(['project','pic','atasan','asisten']);

            $pic     = $document->pic;
            $asisten = $request->user();

            // Kirim email notifikasi ke PIC
            if ($pic && $pic->email) {
                try {
                    $subject = "[Proyek Masuk] {$document->nomor_dokumen} — Tugas Baru untuk Anda";
                    $body    = "Halo {$pic->name},\n\n"
                        . "Anda mendapat tugas baru dari {$asisten->name}.\n\n"
                        . "Detail Dokumen:\n"
                        . "Nomor    : {$document->nomor_dokumen}\n"
                        . "Project  : {$document->project->name}\n"
                        . "Deadline : " . ($document->review_deadline ? Carbon::parse($document->review_deadline)->format('d M Y') : '-') . "\n"
                        . "Dari     : {$asisten->name}\n\n"
                        . "Segera selesaikan sebelum deadline.\n"
                        . "Portal: " . env('APP_URL');

                    Mail::raw($body, function ($mail) use ($pic, $subject) {
                        $mail->to($pic->email)->subject($subject);
                    });
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email ke PIC: " . $e->getMessage());
                }
            }

            return response()->json($document);
        }

        // PIC input return actual date
        if ($request->has('return_actual_date')) {
            $document->return_actual_date = $request->return_actual_date;
            $document->status             = 'selesai';
            $document->submit_status      = 'selesai';
            if ($request->hasFile('file')) {
                $document->file_path = $request->file('file')->store('documents','public');
            }
            $document->save();

            // Kirim email notifikasi ke Asisten bahwa dokumen selesai
            $asisten = $document->atasan;
            $pic     = $document->pic;

            if ($asisten && $asisten->email) {
                try {
                    $subject = "[Selesai] {$document->nomor_dokumen} — Dokumen Telah Dikembalikan PIC";
                    $body    = "Halo {$asisten->name},\n\n"
                        . "Dokumen berikut telah diselesaikan oleh PIC.\n\n"
                        . "Detail Dokumen:\n"
                        . "Nomor          : {$document->nomor_dokumen}\n"
                        . "Project        : {$document->project->name}\n"
                        . "Deadline       : " . ($document->review_deadline ? Carbon::parse($document->review_deadline)->format('d M Y') : '-') . "\n"
                        . "Return Actual  : " . Carbon::parse($request->return_actual_date)->format('d M Y') . "\n"
                        . "Diselesaikan   : " . ($pic ? $pic->name : '-') . "\n\n"
                        . "Silakan cek Portal Monitoring untuk detail lebih lanjut.\n"
                        . "Portal: " . env('APP_URL');

                    Mail::raw($body, function ($mail) use ($asisten, $subject) {
                        $mail->to($asisten->email)->subject($subject);
                    });
                } catch (\Exception $e) {
                    Log::error("Gagal kirim email selesai ke asisten: " . $e->getMessage());
                }
            }

            return response()->json($document->load(['project','pic','atasan','asisten']));
        }

        // Update umum
        $document->update($request->only([
            'nomor_dokumen','project_id','tanggal_masuk','review_deadline','catatan'
        ]));
        if ($request->hasFile('file')) {
            $document->file_path = $request->file('file')->store('documents','public');
            $document->save();
        }
        $document->updateStatus();
        $document->save();
        return response()->json($document->load(['project','pic','atasan','asisten']));
    }

    public function destroy(Document $document) {
        $document->delete();
        return response()->json(['message' => 'Dokumen dihapus']);
    }
}