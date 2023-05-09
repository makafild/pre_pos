<?php

namespace App\Notifications\App;

use Illuminate\Notifications\Notification;

class FirebasePushChannel
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


        if (! $userIds = $notifiable->routeNotificationFor('Fcm')) {
            return;
        }


		$this->sendToFcm($notifiable, $notification, $userIds);
    }

    /**
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @param mixed $targeting
     *
     * @return array
     */
    protected function sendToFcm($notifiable, $notification, $tokens)
    {

		$data = $notification->toFcmPush($notifiable);

		$data['registration_ids'] = $tokens;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
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
                "Authorization : key=AAAAgqTWa9A:APA91bGv8JpaMvi-HfsB6IHKe_mc3Vl1E7Y4jyMty9keIh89VPEQpmQTjR713iGoqzzdP-LnJdSjcTJlFaMR06XSv0xSq7m0JAYVKEIftNh-Ddg_oQRzb0UUsArfXIc5BAx2t6ValLw7"
			),
		));

		$response = curl_exec($curl);

		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			\Log::info($err);
		} else {
//			echo $response;
		}

		return true;
    }
}
