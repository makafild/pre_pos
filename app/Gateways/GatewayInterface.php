<?php
/**
 * Created by PhpStorm.
 * User: iMohammad
 * Date: 6/20/17
 * Time: 8:42 PM
 */

namespace App\Gateways;


interface GatewayInterface
{
    public function setApiToken($token);

    public function request($amount, $redirect, $orderId = null);

    public function verify($transactionId);
}