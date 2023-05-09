<?php

namespace App\Notifications\App;

use Illuminate\Notifications\Notification;

class ChabokPushChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \NotificationChannels\OneSignal\Exceptions\CouldNotSendNotification
     */
    public function send($notifiable, Notification $notification)
    {

        if (! $userIds = $notifiable->routeNotificationFor('ChabokPush')) {
            return;
        }

		$this->sendToChabook($notifiable, $notification, $userIds);
    }

    /**
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @param mixed $targeting
     *
     * @return array
     */
    protected function sendToChabook($notifiable, $notification, $userIds)
    {

		$data = $notification->toChabookPush($notifiable);

		$data['users'] = $userIds;
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://sandbox.push.adpdigital.com/api/push/toUsers?access_token=f49ea61c54079d46a2341f093d5d3f0f5b6276a7",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"accept: application/json",
			),
		));

		$response = curl_exec($curl);

		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
			\Log::info($err);
		} else {
			echo $response;
		}

		return true;
    }
}
