<?php


namespace Packages\ShaunSocial\Chat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSentEvent implements Base
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    protected $user;
    protected $data;
    public $queue = 'broadcast';

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $data)
    {
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('Social.Core.Models.User.'. $this->user->id);
    }

    public function broadcastAs()
    {
        return 'Chat.MessageSentEvent';
    }

    public function broadcastWith()
    {
        return $this->data;
    }
}
