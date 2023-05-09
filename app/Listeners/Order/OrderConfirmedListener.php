<?php

namespace App\Listeners\Order;

use App\Events\Order\ChangeStatusEvent;
use App\Http\Resources\api\Company\v1\Order\OrderResource;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Notifications\App\OrderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderConfirmedListener
{
	/**
	 * The name of the queue the job should be sent to.
	 *
	 * @var string|null
	 */
	public $queue = 'order_confirmed';

	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  ChangeStatusEvent $event
	 *
	 * @return void
	 */
	public function handle(ChangeStatusEvent $event)
	{
		$orderConfirmationSequence = 1;

		if (\Storage::disk('local')->exists('order_confirmation_sequence.txt'))
			$orderConfirmationSequence = \Storage::disk('local')->get('order_confirmation_sequence.txt');

		\Storage::disk('local')->put('order_confirmation_sequence.txt', $orderConfirmationSequence + 1);

		\Log::channel('order_confirmation')->info("start handle order confirmation", ['order_confirmation_sequence' => $orderConfirmationSequence]);

		/** @var Order[] $orders */
		$orders = collect($event->orders);
		\Log::channel('order_confirmation')->info("start handle order 1", ['Err1' => $orders]);

		/** @var User $company */
		$company = $orders[0]->company;

		\Log::channel('order_confirmation')->info("start handle order 2", ['Err2' => $company ]);

		$orderIds = $orders->pluck('id')->all();


		\Log::channel('order_confirmation')->info("start handle order 3", ['Err3' => $orderIds ]);



		/** @var Order[] $orders */
		$orders = Order::whereIn('id', $orderIds)
			->with([
				'Customer.Referrals',
				'Details.Product',
			])
			->get();


		\Log::channel('order_confirmation')->info("start handle order 4", ['Err4' => $orders ]);

		// Rejected order
		if ($event->status == Order::STATUS_REJECTED) {
			Order::whereIn('id', $orderIds)
				->update(['status' => $event->status]);

			foreach ($orders as $order) {
				$notification = new OrderNotification($order, $event->status, $event->text);

				$order->customer->notify($notification);
			}

			\Log::channel('order_confirmation')->info("Start rejected", ['order_confirmation_sequence' => $orderConfirmationSequence]);

			return;
		}

		\Log::channel('order_confirmation')->info("start handle order 5", ['Err5' => '5' ]);

		// Company without API
		if (!$company->api_url || !$company->CompanyToken) {
			Order::whereIn('id', $orderIds)
				->update(['status' => $event->status]);
			\Log::channel('order_confirmation')->info("start handle order token eshtebah", ['Err6' => $company ]);
			return;
		}





		$apiUrl = "{$company->api_url}/prosha/Order/Save";

		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL            => $apiUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 5 * 60,
			CURLOPT_CONNECTTIMEOUT => 20,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => "POST",
			CURLOPT_POSTFIELDS     => json_encode(OrderResource::collection($orders)),
			CURLOPT_HTTPHEADER     => [
				"Accept: application/json",
				"Content-Type: application/json",

				"Authorization: {$company->CompanyToken->token}",
			],
		]);

		\Log::channel('order_confirmation')->info("Send request", [
			'order_confirmation_sequence' => $orderConfirmationSequence,
			'company_id'                  => $company->id,
			'company'                     => $company->name_fa,
			'api_url'                     => $apiUrl,
			'token'                       => $company->CompanyToken->token,
			'data'                        => json_encode(OrderResource::collection($orders)),
		]);
		$response = curl_exec($curl);
		$err      = curl_error($curl);

		curl_close($curl);
		\Log::channel('order_confirmation')->info("End request", ['order_confirmation_sequence' => $orderConfirmationSequence]);


		if ($err) {
			\Log::channel('order_confirmation')->error($err, ['order_confirmation_sequence' => $orderConfirmationSequence]);

			return;
		}

		\Log::channel('order_confirmation')->info($response, ['order_confirmation_sequence' => $orderConfirmationSequence]);
		$response = json_decode($response);
		if (!is_array($response)) {
			return;
		}
		foreach ($orders as $order) {
			$responseOrder = collect($response)->where('id', $order->id)
				->first();

			if ($responseOrder && $responseOrder->status && $responseOrder->orderid) {
				$order->factor_id   = $responseOrder->factor_id ?? NULL;
				$order->tracker_url = $responseOrder->tracker_url ?? NULL;
				$order->referral_id = $responseOrder->orderid;
				$order->status      = $event->status;
				$order->save();

				$notification = new OrderNotification($order, $event->status, $event->text);

				$order->customer->notify($notification);

			}
		}
	}

	/**
	 * Handle a job failure.
	 *
	 * @param  ChangeStatusEvent $event
	 * @param  \Exception        $exception
	 *
	 * @return void
	 */
	public function failed(ChangeStatusEvent $event, $exception)
	{
//		/** @var Order[] $orders */
//		$orders = collect($event->orders);
//
//		$orderIds = $orders->pluck('id')->all();
//
//		Order::whereIn('id', $orderIds)
//			->update(['status' => Order::STATUS_REGISTERED]);
	}
}
