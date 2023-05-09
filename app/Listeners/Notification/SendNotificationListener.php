<?php

namespace App\Listeners\Notification;

use App\Events\Notification\NotificationStoredEvent;
use App\Models\User\OneSignalPlayer;
use App\Models\User\User;
use App\Notifications\App\CustomNotification;
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
	 * @param  NotificationStoredEvent $event
	 * @return void
	 */
	public function handle(NotificationStoredEvent $event)
	{
        $name = 'none';
		$link = NULL;
		if ($event->notification->link) {
			$name = 'link';
			$link = $event->notification->link;
		}

        $notification = new CustomNotification(
            $event->notification->title ?? '',
            $event->notification->message,
            $name,
            $link
        );
//        dd(       $event->notification->title ?? '',
//            $event->notification->message,
//            $name,
//            $link);
        $event->notification->notify($notification);
    }
}
