<?php
/**
 * Created by PhpStorm.
 * User: imohammad
 * Date: 5/3/18
 * Time: 1:39 PM
 */

namespace App\Services;


use App\Models\Setting\Setting;
use App\Models\User\User;

class IranSMSService
{
	static public function send($message, $to)
	{
		if ($to instanceof User) {
			$to = $to->mobile_number;
		}

		try{
			$curl = curl_init();

			$sender = Setting::getSettingBy(Setting::SMS_SENDER);
			$apiKey = Setting::getSettingBy(Setting::SMS_API_KEY);

			curl_setopt_array($curl, array(
                CURLOPT_URL            => "http://api.iransmsservice.com/v2/sms/send/simple",//10002122864121
//				CURLOPT_URL            => "http://api.smsapp.ir/v2/sms/send/simple",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => "POST",
				CURLOPT_POSTFIELDS     => "message={$message}&sender={$sender}&Receptor={$to}&=",
				CURLOPT_HTTPHEADER     => array(
					"apikey:{$apiKey}",
				),
			));
			$response = curl_exec($curl);
			
			$err = curl_error($curl);
			curl_close($curl);

			if ($err) {
				\Log::error("SMS to $to: " . $err);
			} else {
				\Log::info("SMS to $to: " . $response);
				return $response;
			}
		}catch (\Exception $ex){
			\Log::error("SMS to $to: " . $ex->getMessage());
			return;
		}
	}
}