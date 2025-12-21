<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one',
        'user_two',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function getOtherUser($currentUserId)
    {
        return $this->user_one == $currentUserId ? $this->userTwo : $this->userOne;
    }

    public function getLastMessage()
    {
        return $this->messages()
            ->where('is_deleted', false)
            ->latest()
            ->first();
    }

    public function unreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->where('is_deleted', false)
            ->count();
    }

    // Find or create conversation between two users
    public static function findOrCreateBetween($userOneId, $userTwoId)
    {
        // Ensure consistent ordering
        $ids = [$userOneId, $userTwoId];
        sort($ids);

        return self::firstOrCreate(
            ['user_one' => $ids[0], 'user_two' => $ids[1]]
        );
    }
}
