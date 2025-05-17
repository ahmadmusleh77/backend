<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
// استرجاع الرسائل بين مستخدمين
    public function getMessages($sender_id, $receiver_id)
    {
        $messages = Message::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                ->where('receiver_id', $receiver_id);
        })->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                ->where('receiver_id', $sender_id);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }


    // إرسال رسالة جديدة
    public function sendMessage(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|exists:users,user_id',
            'receiver_id' => 'required|exists:users,user_id',
            'content' => 'required|string',
        ]);

        $message = Message::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'content' => $request->content,
        ]);

        return response()->json($message, 201);
    }


    // استرجاع قائمة المحادثات الخاصة بمستخدم
    public function getContacts($user_id)
    {
        $contacts = User::whereHas('sentMessages', function ($query) use ($user_id) {
            $query->where('receiver_id', $user_id);
        })->orWhereHas('receivedMessages', function ($query) use ($user_id) {
            $query->where('sender_id', $user_id);
        })->get();

        return response()->json($contacts);
    }

}
