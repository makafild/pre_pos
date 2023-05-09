<?php

namespace App\Notifications\App;

use App\Models\Order\PeriodicOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class PeriodicOrderNotification extends Notification
{
    use Queueable;
	/**
	 * @var PeriodicOrder
	 */
	private $periodicOrder;

	/**
	 * Create a new notification instance.
	 *
	 * @param PeriodicOrder $periodicOrder
	 */
    public function __construct(PeriodicOrder $periodicOrder)
    {
        //
		$this->periodicOrder = $periodicOrder;
	}

	public function via($notifiable)
	{
		return [
//		    OneSignalChannel::class,
            FirebasePushChannel::class,];
	}

	public function toOneSignal($notifiable)
	{
		return OneSignalMessage::create()
			->subject(trans('notification.periodic_order.title'))
			->body(trans('notification.periodic_order.body'))
			->setData('name', 'periodic_order')
			->setData('id', $this->periodicOrder->id);
	}
    public function toFcmPush($notifiable)
    {
        $content = [
            'message' => trans('notification.periodic_order.body'),
            'header' =>trans('notification.periodic_order.title') ,
            'name' => 'periodic_order',
            "priority"=>"high",
            "data"=>[
                "payload"=>[
                    "name"=>"push",
                    "title"=> trans('notification.periodic_order.title'),
                    "body"=> trans('notification.periodic_order.body'),
                    "id"=> $this->periodicOrder->id,
                ]
            ]
        ];

        return $content;
    }
}
