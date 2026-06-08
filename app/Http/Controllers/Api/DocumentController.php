<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;

class DocumentController extends Controller {
    public function index(Request $request) {
$query = Document::with(['project','pic','atasan','asisten'])
    ->orderBy('created_at','desc');

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

        $data = $request->only(['nomor_dokumen','project_id','catatan','tanggal_masuk']);
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
        // Asisten submit ke manager
        if ($request->has('submit_to_manager')) {
            $document->submit_status = 'submitted';
            $document->save();
            return response()->json($document->load(['project','pic','atasan','asisten']));
        }

        // Manager assign ke PIC
        if ($request->has('assign_to_pic')) {
            $request->validate([
                'pic_id'          => 'required|exists:users,id',
                'review_deadline' => 'required|date',
            ]);
            $document->pic_id          = $request->pic_id;
            $document->atasan_id       = $request->user()->id;
            $document->review_deadline = $request->review_deadline;
            $document->tanggal_masuk   = $request->tanggal_masuk ?? now()->format('Y-m-d');
            $document->submit_status   = 'assigned';
            $document->updateStatus();
            $document->save();
            return response()->json($document->load(['project','pic','atasan','asisten']));
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