<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMessageRequest;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\ChatEvent;
use App\Models\Message;
use App\Models\User;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function getMessages() {
        $messages = Message::with('user')->get();
        // return $messages->toJson(JSON_PRETTY_PRINT);

        return response()->json([
            'messages' => $messages
        ], 200);
    }

    public function sendMessage(SendMessageRequest $req) {
        $user = Auth::user();
        $message = $user->messages()->create([
            'message' => $req->message
        ]);

        broadcast(new ChatEvent($user, $req->message))
            ->toOthers();
    }

    public function editMessage(UpdateMessageRequest $req) {
        $user = Auth::user();
        $oldMessage = $req->oldMessage;
        $newMessage = $req->newMessage;

        $message = Message::where('user_id', $user->id)
            ->where('message', $oldMessage)
            ->orderByDesc('updated_at')
            ->first();
        
        if($message) {
            $message->message = $newMessage;
            $message->save();

            return response()->json([
                'message' => $message
            ], 200);
        } else {
            return response()->json([
                'error' => 'message not found'
            ], 400);
        }
    }

    public function deleteMessage(SendMessageRequest $req) {
        $user = Auth::user();
        $oldMessage = $req->message;

        $message = Message::where('user_id', $user->id)
            ->where('message', $oldMessage)
            ->orderByDesc('updated_at')
            ->first();

        if($message) {
            $message->delete();

            return response()->json([
                'success' => 'message deleted'
            ], 200);
        } else {
            return response()->json([
                'error' => 'message not found'
            ], 400);
        }
    }
}
