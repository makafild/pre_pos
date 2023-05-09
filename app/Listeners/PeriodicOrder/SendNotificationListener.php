<?php

namespace App\Listeners\PeriodicOrder;

use App\Events\PeriodicOrder\PeriodicOrderTimeEvent;
use App\Notifications\App\PeriodicOrderNotification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationListener implements ShouldQueue
{
	/**
	 * The name of the queue the job should be sent to.
	 *
	 * @var string|null
	 */
	public $queue = 'periodic_order_notification';

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
     * @param  PeriodicOrderTimeEvent  $event
     * @return void
     */
    public function handle(PeriodicOrderTimeEvent $event)
    {
		$notification = new PeriodicOrderNotification($event->periodicOrder);

		$event->periodicOrder->customer->notify($notification);
    }
}
