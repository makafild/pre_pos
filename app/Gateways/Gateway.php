<?php
/**
 * Created by PhpStorm.
 * User: iMohammad
 * Date: 6/21/17
 * Time: 11:02 AM
 */

namespace App\Gateways;


class Gateway
{
    /**
     * @param $gateway
     * @return GatewayInterface
     */
    public static function driver($gateway)
    {
        switch ($gateway) {
            case 'ir_sep':
				return new ir_sep();
            	break;
            case 'ir_pay':
            default:
                return new ir_pay();
                break;
        }
    }
}