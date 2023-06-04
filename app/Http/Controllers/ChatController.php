<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChatMessage;
use App\Events\NewChatMessage;
use App\Events\ReadChatMessages;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\ReactOnMessageRequest;
use Kutia\Larafirebase\Facades\Larafirebase;

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
        $message->unread = true;
        $message->sender_id = auth()->id();
        $message->message = $request->message;
        $message->recipient_id = $recipient->id;
        $message->save();

        try {
            broadcast(new NewChatMessage($message));
        } catch (\Exception $e) {
            logger("error while broadcasting message", [$e]);
        }

        // send notification using firebase
        if ($recipient->fcm_token) $this->sendMessageNotification($recipient->fcm_token, $message->message);

        return response()->json(['message' => $message]);
    }

    public function sendMessageNotification($token, $message)
    {
        Larafirebase::withTitle(auth()->user()->name . ' ارسل لك رسالة')
            ->withBody($message)
            ->withSound('default')
            ->withPriority('high')
            ->withClickAction(env('FRONT_END_URL') . '/chat')
            ->sendNotification($token);
    }

    public function markPrevMessagesRead(User $sender)
    {
        if (!auth()->user()->isFriendWith($sender)) {
            abort(403);
        }

        // read all prev messages
        auth()->user()->receivedMessages()->where('sender_id', $sender->id)->update(['unread' => false]);

        try {
            broadcast(new ReadChatMessages($sender->id, auth()->id()));
        } catch (\Exception $e) {
            logger("error while broadcasting read messages", [$e]);
        }
    }

    public function reactOnMessage(ChatMessage $message, ReactOnMessageRequest $request)
    {
        $sender = User::find($message->sender_id);
        if (!auth()->user()->isFriendWith($sender)) abort(403);

        if ($request->reaction === $message->reaction) {
            $message->reaction = null;
            $message->save();
        } else {
            $message->reaction = $request->reaction;
            $message->save();
        }

        return response()->json(['message' => $message]);
    }
}
