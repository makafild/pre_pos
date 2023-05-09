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

class MessageStored implements ShouldBroadcast
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	private $messageEntity;

	public $message;
	public $from;
	public $to;

	/**
	 * Create a new event instance.
	 *
	 * @param Message $message
	 */
	public function __construct(Message $message)
	{
		$this->messageEntity = $message;

		$this->message = $this->messageEntity->message;
		$this->from = $this->messageEntity->from_id;
		$this->to = $this->messageEntity->to_id;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return \Illuminate\Broadcasting\Channel|array
	 */
	public function broadcastOn()
	{
		return new Channel('messages.' . $this->messageEntity->to_id);
	}
}
