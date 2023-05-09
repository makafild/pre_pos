<?php
/**
 * Created by PhpStorm.
 * User: iMohammad
 * Date: 6/20/17
 * Time: 8:40 PM
 */

namespace App\Gateways;

use nusoap_client;

class ir_sep implements GatewayInterface
{
	private $apiKey;
	private $sendUrl;
	private $gatewayUrl;
	private $verifyUrl;

	function __construct()
	{
		$this->apiKey     = env('SEP_IR_api_key');
		$this->sendUrl    = env('SEP_IR_send_url');
		$this->gatewayUrl = env('SEP_IR_gateway_url');
		$this->verifyUrl  = env('SEP_IR_verify_url');
	}

	public function setApiToken($token)
	{
		$this->apiKey = $token;

		return $this;
	}

	public function request($amount, $redirect, $orderId = NULL)
	{
		$data = [
			'TermID'      => $this->apiKey,
			'ResNum'      => $orderId,
			'TotalAmount' => $amount,
		];

		$soapClient = new \SoapClient($this->sendUrl);
		$token      = $soapClient->__call("RequestToken", $data);

		if ($token) {
			return [
				'method'         => 'post',
				'gateway_url'    => $this->gatewayUrl,
				'transaction_id' => $token,
				'redirect_url'   => $redirect,
			];
		}

		return false;
	}

	public function verify($transactionId)
	{
		$data = [
			$transactionId,
			$this->apiKey,
		];

		$soapClient = new \SoapClient("https://sep.shaparak.ir/payments/referencepayment.asmx?wsdl");
		$value      = $soapClient->__call("verifyTransaction", $data);

		if ($value < 0) {
			return false;
		}

		return true;
	}
}