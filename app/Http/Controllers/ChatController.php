<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\BroadcastMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    // Chat access rules based on roles
    private $chatRules = [
        'superadmin' => ['supplier', 'factory', 'distributor', 'courier'],
        'supplier' => ['superadmin', 'factory', 'courier'],
        'factory' => ['superadmin', 'supplier', 'distributor', 'courier'],
        'distributor' => ['superadmin', 'factory', 'courier'],
        'courier' => ['superadmin', 'supplier', 'factory', 'distributor'],
    ];

    public function index()
    {
        $user = auth()->user();
        
        // Get all conversations for current user
        $conversations = Conversation::where('user_one', $user->id)
            ->orWhere('user_two', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $otherUser = $conv->getOtherUser($user->id);
                $lastMessage = $conv->getLastMessage();
                return [
                    'id' => $conv->id,
                    'other_user' => $otherUser,
                    'last_message' => $lastMessage,
                    'unread_count' => $conv->unreadCount($user->id),
                ];
            });

        // Get broadcasts (only last 7 days)
        $broadcasts = BroadcastMessage::with('sender')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->get();

        return view('chat.index', compact('conversations', 'broadcasts'));
    }

    public function getContacts()
    {
        $user = auth()->user();
        $allowedRoles = $this->chatRules[$user->role] ?? [];
        
        $contacts = User::whereIn('role', $allowedRoles)
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get()
            ->map(function ($contact) {
                return [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'role' => $contact->role,
                    'avatar' => $contact->avatar,
                ];
            });

        return response()->json($contacts);
    }

    public function show($userId)
    {
        $user = auth()->user();
        $otherUser = User::findOrFail($userId);

        // Check if chat is allowed
        $allowedRoles = $this->chatRules[$user->role] ?? [];
        if (!in_array($otherUser->role, $allowedRoles)) {
            return redirect()->route('chat.index')->with('error', 'You cannot chat with this user.');
        }

        // Find or create conversation
        $conversation = Conversation::findOrCreateBetween($user->id, $userId);

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        // Get all conversations for sidebar
        $conversations = Conversation::where('user_one', $user->id)
            ->orWhere('user_two', $user->id)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conv) use ($user) {
                $other = $conv->getOtherUser($user->id);
                $lastMessage = $conv->getLastMessage();
                return [
                    'id' => $conv->id,
                    'other_user' => $other,
                    'last_message' => $lastMessage,
                    'unread_count' => $conv->unreadCount($user->id),
                ];
            });

        // Get broadcasts (only last 7 days)
        $broadcasts = BroadcastMessage::with('sender')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->get();

        return view('chat.index', [
            'conversations' => $conversations,
            'activeConversation' => $conversation,
            'activeUser' => $otherUser,
            'broadcasts' => $broadcasts,
        ]);
    }

    public function getMessages($conversationId)
    {
        $user = auth()->user();
        $conversation = Conversation::findOrFail($conversationId);

        // Verify user is part of conversation
        if ($conversation->user_one != $user->id && $conversation->user_two != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Mark messages as read
        Message::where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->is_deleted ? null : $msg->message,
                    'image_path' => $msg->is_deleted ? null : $msg->image_path,
                    'is_deleted' => $msg->is_deleted,
                    'is_mine' => $msg->sender_id == $user->id,
                    'sender_name' => $msg->sender->name,
                    'sender_avatar' => $msg->sender->avatar,
                    'created_at' => $msg->created_at->format('M d, H:i'),
                    'can_unsend' => $msg->canUnsend($user->id),
                ];
            });

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:5120',
        ]);

        $user = auth()->user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Verify user is part of conversation
        if ($conversation->user_one != $user->id && $conversation->user_two != $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Message or image is required'], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('chat_images', 'public');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'message' => $message->message,
                'image_path' => $message->image_path,
                'is_mine' => true,
                'sender_name' => $user->name,
                'sender_avatar' => $user->avatar,
                'created_at' => $message->created_at->format('M d, H:i'),
                'can_unsend' => true,
            ],
        ]);
    }

    public function unsendMessage($messageId)
    {
        $user = auth()->user();
        $message = Message::findOrFail($messageId);

        if (!$message->canUnsend($user->id)) {
            return response()->json(['error' => 'Cannot unsend this message'], 403);
        }

        if ($message->image_path) {
            Storage::disk('public')->delete($message->image_path);
        }

        $message->update([
            'is_deleted' => true,
            'message' => null,
            'image_path' => null,
        ]);

        return response()->json(['success' => true]);
    }

    // Broadcast message to all users (superadmin only)
    public function sendBroadcast(Request $request)
    {
        $user = auth()->user();
        
        if ($user->role !== 'superadmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'nullable|string|max:5000',
            'image' => 'nullable|image|max:5120',
        ]);

        if (!$request->message && !$request->hasFile('image')) {
            return response()->json(['error' => 'Message or image is required'], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('broadcast_images', 'public');
        }

        $broadcast = BroadcastMessage::create([
            'sender_id' => $user->id,
            'message' => $request->message,
            'image_path' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'broadcast' => [
                'id' => $broadcast->id,
                'message' => $broadcast->message,
                'image_path' => $broadcast->image_path,
                'sender_name' => $user->name,
                'created_at' => $broadcast->created_at->format('M d, H:i'),
            ],
        ]);
    }

    // Get all broadcasts
    public function getBroadcasts()
    {
        $broadcasts = BroadcastMessage::with('sender')
            ->orderByDesc('created_at')
            ->take(50)
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'message' => $b->message,
                    'image_path' => $b->image_path,
                    'sender_name' => $b->sender->name,
                    'sender_avatar' => $b->sender->avatar,
                    'created_at' => $b->created_at->format('M d, H:i'),
                ];
            });

        return response()->json($broadcasts);
    }
}
