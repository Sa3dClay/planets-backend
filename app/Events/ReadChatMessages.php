<?php

namespace App\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReadChatMessages implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sender_id;
    public $recipient_id;

    public function __construct($sender_id, $recipient_id)
    {
        $this->sender_id = $sender_id;
        $this->recipient_id = $recipient_id;
    }

    public function broadcastOn()
    {
        return new Channel('read.' . $this->sender_id . '-' . $this->recipient_id);
    }

    public function broadcastAs()
    {
        return 'read-chat-messages';
    }
}
