<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\ChatEvent;
use App\Models\User;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function sendMessage(Request $req) {
        $user = Auth::user();
        
        broadcast(new ChatEvent($user, $req->message))
            ->toOthers();
    }

    // public function sendMessage() {
    //     $user = Auth::user();
    //     $message = "Hello World!";
    //     event(new ChatEvent($user, $message));
    // }
}
