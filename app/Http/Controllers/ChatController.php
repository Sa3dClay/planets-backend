<?php

namespace App\Http\Controllers;

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

    // public function sendMessage() {
    //     $user = Auth::user();
    //     $message = "Hello World!";
    //     event(new ChatEvent($user, $message));
    // }
}
