<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Project;
use App\Models\NotificationLog;

class DashboardController extends Controller {
    public function index() {
        // Update status semua dokumen yang belum selesai dulu
        Document::whereNull('return_actual_date')->each(function($doc) {
            $doc->updateStatus();
            $doc->save();
        });

        $total        = Document::count();
        $earlyWarning = Document::where('status', 'early_warning')->count();
        $alert        = Document::where('status', 'alert')->count();
        $selesai      = Document::where('status', 'selesai')->count();

        $projects = Project::withCount([
            'documents',
            'documents as selesai_count'      => fn($q) => $q->where('status','selesai'),
            'documents as early_warning_count' => fn($q) => $q->where('status','early_warning'),
            'documents as alert_count'         => fn($q) => $q->where('status','alert'),
            'documents as pending_count'       => fn($q) => $q->where('status','pending'),
        ])->get();

        $recentLogs = NotificationLog::with(['document.project','pic'])
            ->orderBy('sent_at','desc')
            ->limit(10)
            ->get();

        return response()->json([
            'summary'    => compact('total','earlyWarning','alert','selesai'),
            'projects'   => $projects,
            'recentLogs' => $recentLogs,
        ]);
    }
}