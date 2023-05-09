<?php

namespace App\Listeners\Order;

use App\Events\Order\ChangeStatusEvent;
use App\Notifications\App\OrderNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationListener 
{
	/**
	 * The name of the queue the job should be sent to.
	 *
	 * @var string|null
	 */
	public $queue = 'notification';

	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  ChangeStatusEvent $event
	 * @return void
	 */
	public function handle(ChangeStatusEvent $event)
	{
		foreach ($event->orders as $order) {
			$notification = new OrderNotification($order, $event->status, $event->text);

			$order->customer->notify($notification);
		}
	}
}
