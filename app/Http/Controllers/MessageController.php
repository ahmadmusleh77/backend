<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function getChatContacts()
    {
        $userId = Auth::id();

        $userIdsWithMessages = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->flatMap(function ($msg) use ($userId) {
                return [$msg->sender_id == $userId ? $msg->receiver_id : $msg->sender_id];
            })
            ->unique();

        $contacts = User::whereIn('user_id', $userIdsWithMessages)->get()->map(function ($user) use ($userId) {
            $messages = Message::where(function ($query) use ($userId, $user) {
                $query->where('sender_id', $userId)->where('receiver_id', $user->user_id)
                    ->orWhere('sender_id', $user->user_id)->where('receiver_id', $userId);
            })
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($msg) use ($userId) {
                    return [
                        'text' => $msg->content,
                        'sender' => $msg->sender_id == $userId ? 'user' : 'other',
                        'time' => $msg->created_at->format('h:i a'),
                    ];
                });

            return [
                'id' => $user->user_id,
                'name' => $user->name,
                'avatar' => $user->avatar ?? 'default.jpg',
                'messages' => $messages
            ];
        });

        return response()->json($contacts);
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,user_id',
            'content' => 'required|string|max:1000',
        ]);



        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $validated['receiver_id'],
            'content' => $validated['content'],
        ]);

        //notification
        $sender = User::find(auth()->id());
        $receiver =User::find($validated['receiver_id']);
        $notificationController= app(NotificationController::class);
        if ($sender->user_type === 'job_holder' && $receiver->user_type === 'craftsman') {
            $notificationController->notifyNewMessageToCraftsman($receiver, $sender, $validated['content']);
        } elseif ($sender->user_type === 'craftsman' && $receiver->user_type === 'job_holder') {
            $notificationController->notifyNewMessageToJobHolder($receiver, $sender, $validated['content']);
        }


        return response()->json([
            'status' => 200,
            'message' => 'Message sent successfully',
            'data' => [
                'text' => $message->content,
                'sender' => 'user',
                'time' => $message->created_at->format('h:i a'),
            ]
        ]);
    }
    public function getSingleContact($id, Request $request)
    {
        $authUser = $request->user();
        $targetUser = User::find($id);

        if (!$targetUser) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($authUser->id === $targetUser->id) {
            return response()->json(['message' => 'You cannot chat with yourself'], 400);
        }

        return response()->json([
            'id' => $targetUser->id,
            'name' => $targetUser->name,
            'avatar' => $targetUser->avatar
                ? asset('storage/' . $targetUser->avatar)
                : asset('storage/default.jpg'),
            'messages' => []
        ]);
    }

}
