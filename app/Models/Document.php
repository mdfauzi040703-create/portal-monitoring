<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model {
    use HasFactory;

    protected $fillable = [
        'nomor_dokumen','project_id','atasan_id','asisten_id','pic_id',
        'tanggal_masuk','review_deadline','return_actual_date',
        'status','submit_status','catatan','file_path'
    ];

    protected $casts = [
        'tanggal_masuk'      => 'date',
        'review_deadline'    => 'date',
        'return_actual_date' => 'date',
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }
    public function pic() {
        return $this->belongsTo(User::class, 'pic_id');
    }
    public function atasan() {
        return $this->belongsTo(User::class, 'atasan_id');
    }
    public function asisten() {
        return $this->belongsTo(User::class, 'asisten_id');
    }
    public function notificationLogs() {
        return $this->hasMany(NotificationLog::class);
    }
    public function updateStatus() {
        if ($this->return_actual_date) {
            $this->status = 'selesai';
            return;
        }
        if (!$this->review_deadline) {
            $this->status = 'pending';
            return;
        }
        $today    = now()->startOfDay();
        $deadline = \Carbon\Carbon::parse($this->review_deadline)->startOfDay();
        $diff     = (int) round($today->diffInDays($deadline, false));

        if ($diff === 1)    $this->status = 'early_warning';
        elseif ($diff <= 0) $this->status = 'alert';
        else                $this->status = 'pending';
    }
}