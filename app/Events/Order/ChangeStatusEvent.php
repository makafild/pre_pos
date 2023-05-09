<?php

namespace App\Events\Order;

use App\Models\Order\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ChangeStatusEvent
{
	use Dispatchable, InteractsWithSockets, SerializesModels;

	/**
	 * @var Order[]
	 */
	public $orders;
	/**
	 * @var string
	 */
	public  $text;
	/**
	 * @var string
	 */
	public $status;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */
	public function __construct($orders, $status, $text = '')
	{
		if (!is_array($orders))
			$orders = [$orders];

		$this->orders = $orders;
		$this->text   = $text;
		$this->status = $status;
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
