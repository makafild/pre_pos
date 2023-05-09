<?php
/**
 * Created by PhpStorm.
 * User: iMohammad
 * Date: 6/20/17
 * Time: 8:40 PM
 */

namespace App\Gateways;

class ir_pay implements GatewayInterface
{
	private $apiKey;
	private $sendUrl;
	private $gatewayUrl;
	private $verifyUrl;

	function __construct()
	{
		$this->apiKey = env('PAY_IR_api_key');
		$this->sendUrl = env('PAY_IR_send_url');
		$this->gatewayUrl = env('PAY_IR_gateway_url');
		$this->verifyUrl = env('PAY_IR_verify_url');
	}

	public function setApiToken($token)
	{
		$this->apiKey = $token;

		return $this;
	}

	public function request($amount, $redirect, $orderId = null)
	{
		\Log::info($redirect);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->sendUrl);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$this->apiKey&amount=$amount&redirect=$redirect");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($response);

		if ($response->status) {
			$gatewayUrl = $this->gatewayUrl . $response->transId;

			return [
				'transaction_id' => $response->transId,
				'gateway_url'    => $gatewayUrl,
			];
		}

		\Log::error(json_encode($response));

		return false;

	}

	public function verify($transactionId)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->verifyUrl);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "api=$this->apiKey&transId=$transactionId");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($res);

		if ($response->status) {
			return true;
		}

		\Log::error("$response");

		return false;

	}
}