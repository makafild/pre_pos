<?php

namespace App\Notifications\App;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class CustomNotification extends Notification
{
	use Queueable;

	private $title;
	private $message;
	private $name;
	private $link;
	private $company_id;
	private $product_id;

	/**
	 * Create a new notification instance.
	 *
	 * @param $message
	 * @param $name
	 * @param $link
	 * @param $company_id
	 * @param $product_id
	 */
	public function __construct($title, $message, $name, $link = NULL, $company_id = NULL, $product_id = NULL)
	{
		$this->title      = $title;
		$this->message    = $message;
		$this->name       = $name;
		$this->link       = $link;
		$this->company_id = $company_id;
		$this->product_id = $product_id;
	}

	public function via($notifiable)
	{
		return [
//			OneSignalChannel::class,
			ChabokPushChannel::class,
			FirebasePushChannel::class,
		];
	}

	public function toOneSignal($notifiable)
	{
		$notification = OneSignalMessage::create()
			->subject($this->title)
			->body($this->message)
			->setData('name', $this->name)
			->icon(env('APP_URL') . '/logo.png');

		switch ($this->name) {
			case 'link':
				$notification->setData('link', $this->link);
				break;
			case 'company':
				$notification->setData('company_id', $this->company_id);
				break;
			case 'product':
				$notification->setData('company_id', $this->company_id)
					->setData('product_id', $this->product_id);
				break;
		}

		return $notification;
	}

	public function toChabookPush($notifiable)
	{
		$content = [
		    "content"=>$this->message,
            "vibrate"=>[10, 20, 30, 40],
            "sound"=> "toy.mp3",
			'notification' => [
			    "title"=>	$this->title,
                "body"=>	$this->message
            ],"data"=>[]
		];

		switch ($this->name) {
			case 'link':
				$content['data']['link'] = $this->link;
				break;
			case 'company':
				$content['data']['company_id'] = $this->company_id;
				break;
			case 'product':
				$content['data']['company_id'] = $this->company_id;
				$content['data']['product_id'] = $this->product_id;
				break;
		}

		return $content;
	}
    public function toFcmPush($notifiable)
    {  
        $content = [
            'message' => $this->message,
            'header' => $this->title ,
            'name' => $this->name ,
            "priority"=>"high",
            "data"=>[
                "payload"=>[
                    "name"=>"push",
                    "title"=> $this->title,
                    "body"=> $this->message,
                ]
            ]
        ];

        switch ($this->name) {
            case 'link':
                $content['data']['link'] = $this->link;
                break;
            case 'company':
                $content['data']['company_id'] = $this->company_id;
                break;
            case 'product':
                $content['data']['company_id'] = $this->company_id;
                $content['data']['product_id'] = $this->product_id;
                break;
        }

        return $content;
    }
}
