<?php

namespace App\Listeners\Order;

use App\Events\Order\ChangeStatusEvent;
use App\Events\Order\OrderSendToRobot;
use App\Http\Resources\api\Company\v1\Order\OrderResource;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Notifications\App\OrderNotification;
use Core\Packages\robots\CompanyToken;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendToRobotOrder
{
    public function __construct()
    {
        //
    }


    public function handle(OrderSendToRobot $event)
    {


        $order = Order::with(['Details.Product', 'Customer', 'Company'])->where('id', $event->order_id)->first(); //->where('company_id',$this->ISCompany())->first();
        $url = $order->company->api_url . "";
        $token=CompanyToken::where('company_id',$order->company->id)->first();
        $orderFormat = array();
        $customer_id=User::find($order->customer->id);
        $orderFormat = [
            "id" => $order->id,
            "customer_id" => $customer_id->referral_id,
            "payment_method_id" => $order->payment_method_id,
            "price_without_promotions" => $order->price_without_promotions,
            "discount" => $order->discount,
            "amount_promotion" => $order->amount_promotion,
            "final_price" => $order->final_price,
            "date_of_sending" => $order->date_of_sending,
            "detalis" => array()
        ];


        foreach ($order->details as $detali) {
            $orderFormat['detalis'][] = [
                "product_id" => $detali['Product']['referral_id'],
                "master" => $detali['master'],
                "slave" => $detali['slave'],
                "slave2" => $detali['slave2'],
                "total" => $detali['total'],
                "price" => $detali['price'],
                "discount" => $detali['discount'],
                "final_price" => $detali['final_price'],
                "prise" => $detali['prise']

            ];
        }
        $data=['token'=>$token->token,'order'=> $orderFormat];

        $curl = curl_init();

        $url_robot=$url.'/order';

        // curl_setopt_array($curl, array(
        //     CURLOPT_URL => $url_robot,
        //     CURLOPT_RETURNTRANSFER => true,
        //     CURLOPT_ENCODING => '',
        //     CURLOPT_MAXREDIRS => 10,
        //     CURLOPT_TIMEOUT => 0,
        //     CURLOPT_FOLLOWLOCATION => true,
        //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //     CURLOPT_CUSTOMREQUEST => 'POST',
        //     CURLOPT_POSTFIELDS => $data,
        //     CURLOPT_HTTPHEADER => array(
        //         'accept: application/json, text/plain, */*',
        //         'origin: ',
        //         'Content-Type: application/json'
        //     ),
        // ));
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url_robot,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
             CURLOPT_POSTFIELDS =>json_encode($data),
            //  CURLOPT_POSTFIELDS => json_encode($data,true),
           //  CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'accept: application/json, text/plain, */*',
                'origin: ',
                'Content-Type: application/json'
            ),
        ));

       $response2 = curl_exec($curl);
       $response=json_decode($response2);

        curl_close($curl);
          
        if($response->status=="200")
        {
            $order->referral_id=$response->order_id;
            $order->save();
        }
        dd($response2);
    }
}
