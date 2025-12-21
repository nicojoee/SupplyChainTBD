<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'message',
        'image_path',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
