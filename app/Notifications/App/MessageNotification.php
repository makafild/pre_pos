<?php

namespace App\Notifications\App;

use App\Models\Common\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class MessageNotification extends Notification
{
    use Queueable;

    /**
     * @var Message
     */
    private $message;

    /**
     * Create a new notification instance.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function via($notifiable)
    {
        return [FirebasePushChannel::class,];
    }

//    public function toOneSignal($notifiable)
//    {
//        $title = trans('notification.message.title', [
//            'user' => $this->message->From->title,
//        ]);
//

//
//        return OneSignalMessage::create()
//            ->subject($title)
//            ->body($body)
//            ->setData('name', 'message')
//            ->setData('id', $this->message->from_id)
//            ->setData('title', $this->message->From->title);
//    }
    public function toFcmPush($notifiable)
    {
        $title = trans('notification.message.title', [
            'user' => $this->message->From->title,
        ]);
        $body = trans('notification.message.body', [
            'message' => $this->message->message,
        ]);
        $content = [
            'message' => trans('notification.company_report.body'),
            'header' =>trans('notification.company_report.title') ,
            'name' =>  'company_report' ,
            "priority"=>"high",
            "data"=>[
                "payload"=>[
                    "name"=>"push",
                    "title"=> $title,
                    "body"=> $body,
                    "id"=> $this->message->from_id
                ]
            ]
        ];

        return $content;
    }
}
