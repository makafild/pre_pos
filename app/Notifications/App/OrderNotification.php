<?php

namespace App\Notifications\App;

use App\Models\Order\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalWebButton;

class OrderNotification extends Notification
{
	use Queueable;

	/**
	 * @var Order
	 */
	private $order;
	/**
	 * @var string
	 */
	private $text;
	private $status;

	public function __construct(Order $order, $status, $text = '')
	{

		$this->order = $order;
		$this->text = $text;
		$this->status = $status;
	}

	public function via($notifiable)
	{
		return [
            FirebasePushChannel::class
        ];
	}

//	public function toOneSignal($notifiable)
//	{
//		\Log::info(trans('notification.order.body', [
//				'id'     => $this->order->id,
//				'status' => trans("translate.order.order.{$this->status}"),
//			]) .  ($this->text ? ' توضیح: ' . $this->text : ''));
//		return OneSignalMessage::create()
//			->subject(trans('notification.order.title'))
//			->body(trans('notification.order.body', [
//				'id'     => $this->order->id,
//				'status' => trans("translate.order.order.{$this->status}"),
//			]) .  ($this->text ? ' توضیح: ' . $this->text : ''))
//			->setData('name', 'order')
//			->setData('id', $this->order->id);
//	}
    public function toFcmPush($notifiable)
    {

        $content = [
            'message' => trans('notification.order.body', [
                    'id'     => $this->order->id,
                    'status' => trans("translate.order.order.{$this->status}"),
                ]) .  ($this->text ? ' توضیح: ' . $this->text : ''),
            'header' =>trans('notification.order.title') ,
            'name' => 'order',
            "priority"=>"high",
            "data"=>[
                "payload"=>[
                    "name"=>"push",
                    "title"=>trans('notification.order.title') ,
                    "body"=>trans('notification.order.body', [
                            'id'     => $this->order->id,
                            'status' => trans("translate.order.order.{$this->status}"),
                        ]) .  ($this->text ? ' توضیح: ' . $this->text : ''),
                    "id"=>$this->order->id,
                ]
            ]
        ];

        return $content;
    }
}
