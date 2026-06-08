<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password','whatsapp','role','atasan_id'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function atasan() {
        return $this->belongsTo(User::class, 'atasan_id');
    }
    public function bawahan() {
        return $this->hasMany(User::class, 'atasan_id');
    }
    public function dokumenSebagaiPIC() {
        return $this->hasMany(Document::class, 'pic_id');
    }
    public function dokumenSebagaiAtasan() {
        return $this->hasMany(Document::class, 'atasan_id');
    }
}