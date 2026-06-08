<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model {
    use HasFactory;

    protected $fillable = ['name','description','created_by'];

    public function documents() {
        return $this->hasMany(Document::class);
    }
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }
}