<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
              ->orWhereNull('target_asisten_id'); // fallback untuk data lama
        });
    }

    if ($request->status)     $query->where('status', $request->status);
    if ($request->project_id) $query->where('project_id', $request->project_id);
    if ($request->search)     $query->where('nomor_dokumen','like','%'.$request->search.'%');
    if ($request->submit_status) $query->where('submit_status', $request->submit_status);
    if ($request->asisten_id) $query->where('asisten_id', $request->asisten_id);
    if ($request->atasan_id)  $query->where('atasan_id', $request->atasan_id);
    if ($request->pic_id)     $query->where('pic_id', $request->pic_id);

    return response()->json($query->get());
}

public function store(Request $request) {
    $request->validate([
        'nomor_dokumen' => 'required|string',
        'project_id'    => 'required|exists:projects,id',
    ]);

    $data = $request->only(['nomor_dokumen','project_id','catatan','tanggal_masuk','review_deadline']);
    $data['asisten_id']    = $request->user()->id; // sekarang diisi Manager
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
// Manager submit ke Asisten Manager yang dipilih
if ($request->has('submit_to_manager')) {
    $request->validate([
        'target_asisten_id' => 'required|exists:users,id',
    ]);
    $document->target_asisten_id = $request->target_asisten_id;
    $document->submit_status     = 'submitted';
    $document->save();
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

    // Kirim notifikasi ke PIC
    $pic     = $document->pic;
    $project = $document->project;
    $asisten = $request->user();

    $message = "[Proyek Masuk] Anda mendapat tugas baru\n"
        . "Nomor   : {$document->nomor_dokumen}\n"
        . "Project : {$project->name}\n"
        . "Deadline: " . ($document->review_deadline ? \Carbon\Carbon::parse($document->review_deadline)->format('d M Y') : '-') . "\n"
        . "Dari    : {$asisten->name}\n"
        . "Segera selesaikan sebelum deadline.";

    // Kirim Email ke PIC
    if ($pic && $pic->email) {
        try {
            \Illuminate\Support\Facades\Mail::raw($message, function ($mail) use ($pic, $document) {
                $mail->to($pic->email)
                     ->subject("[Proyek Masuk] {$document->nomor_dokumen} — Tugas Baru untuk Anda");
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal kirim email proyek masuk: " . $e->getMessage());
        }
    }

    // Kirim WhatsApp ke PIC via Fonnte
    if ($pic && $pic->whatsapp) {
        try {
            \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => env('FONNTE_TOKEN'),
            ])->post('https://api.fonnte.com/send', [
                'target'  => $pic->whatsapp,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal kirim WA proyek masuk: " . $e->getMessage());
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