<?php

namespace App\Events\Notification;

use App\Models\Common\Message;
use App\Models\Common\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationStoredEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
	/**
	 * @var Notification
	 */
	public $notification;

	/**
	 * Create a new event instance.
	 *
	 * @param Notification $notification
	 */
    public function __construct(Notification $notification)
    {
		$this->notification = $notification;
	}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
