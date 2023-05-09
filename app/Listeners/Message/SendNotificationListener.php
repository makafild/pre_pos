<?php

namespace App\Listeners\Message;

use App\Events\Message\MessageStoredEvent;
use App\Notifications\App\MessageNotification;
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
		\Log::info("SendNotificationListener");
	}

	/**
	 * Handle the event.
	 *
	 * @param  MessageStoredEvent $event
	 * @return void
	 */
	public function handle(MessageStoredEvent $event)
	{
		\Log::info("----------------------- SendNotificationListener -------------------------------");
		\Log::info($event->message->To);

		if ($event->message->To->isCustomer()) {
			$notification = new MessageNotification($event->message);

			$event->message->To->notify($notification);
		}

	}
}
