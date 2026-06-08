<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;

class NotificationLogController extends Controller {
    public function index() {
        $logs = NotificationLog::with(['document','pic'])
            ->orderBy('sent_at','desc')
            ->paginate(20);
        return response()->json($logs);
    }
}