<?php

namespace App\Events\Message;

use App\Models\Common\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageStoredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public $message;

	/**
	 * Create a new event instance.
	 *
	 * @param Message $message
	 */
	public function __construct(Message $message)
	{
		$this->message = $message;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
	 */
	public function broadcastOn()
	{
		return new Channel('messages');
	}
}
