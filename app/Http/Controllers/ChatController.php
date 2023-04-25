<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatMessage;
use App\Events\NewChatMessage;
use App\Http\Requests\SendMessageRequest;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getMessages(User $recipient)
    {
        if (!auth()->user()->isFriendWith($recipient)) {
            abort(403);
        }

        $messages = ChatMessage::whereIn('sender_id', [auth()->user()->id, $recipient->id])
            ->whereIn('recipient_id', [auth()->user()->id, $recipient->id])->get();

        return response()->json(['messages' => $messages]);
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $recipient = User::findOrFail($request->recipient_id);
        if (!auth()->user()->isFriendWith($recipient)) {
            abort(403);
        }

        $message = new ChatMessage;
        $message->sender_id = auth()->id();
        $message->message = $request->message;
        $message->recipient_id = $recipient->id;
        $message->save();

        broadcast(new NewChatMessage($message));

        return response()->json(['message' => $message]);
    }
}
