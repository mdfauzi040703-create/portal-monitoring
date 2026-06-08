<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationLog extends Model {
    use HasFactory;

    protected $fillable = [
        'document_id','pic_id','notif_type',
        'channel','status','message_body','sent_at'
    ];

    protected $casts = ['sent_at' => 'datetime'];

    public function document() {
        return $this->belongsTo(Document::class);
    }
    public function pic() {
        return $this->belongsTo(User::class, 'pic_id');
    }
}