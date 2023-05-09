<?php

namespace App\Events\PeriodicOrder;

use App\Models\Order\PeriodicOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PeriodicOrderTimeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
	/**
	 * @var PeriodicOrder
	 */
	public $periodicOrder;

	/**
	 * Create a new event instance.
	 *
	 * @param PeriodicOrder $periodicOrder
	 */
    public function __construct(PeriodicOrder $periodicOrder)
    {
        //
		$this->periodicOrder = $periodicOrder;
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
