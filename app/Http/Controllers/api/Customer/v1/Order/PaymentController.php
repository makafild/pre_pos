<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use App\Gateways\Gateway;
use App\Http\Controllers\Controller;
use App\Models\Billing\Charge;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function pay($orderId)
	{
		/** @var Order $order */
		$order = Order::where([
			'id'          => $orderId,
			'customer_id' => auth()->id(),
		])->first();

		if (!$order)
			return [
				'statue' => false,
			];

		$order->payment_confirm = Order::PAYMENT_DEPENDING;
		$order->save();

		// new charge record
		$charge           = new Charge();
		$charge->amount   = $order->final_price;
		$charge->payment  = $order->final_price;
		$charge->method   = 'ir_sep';
		$charge->status   = Charge::STATUS_PENDING;
		$charge->order_id = $order->id;
		$charge->user()->associate(auth('mobile')->user());
		$charge->save();

		// request to gateway
		$driver   = Gateway::driver($charge->method);
		$response = $driver->setApiToken($order->company->gateway_token)
			->request($charge->payment, env('APP_URL') . "/payment/order_verify?gateway={$charge->method}", $order->id);

		if ($response) {
			$charge->transaction_id = $response['transaction_id'];
			$charge->status         = Charge::STATUS_GATEWAY;
			$charge->save();

			$cacheKey = "sep_" . Str::random();
			\Cache::put($cacheKey, $response['transaction_id'], 1);

			return [
				'status'         => true,
				'gateway_url'    => route('sepForm', ['token' => $cacheKey]),
			];
		} else {
			$charge->status = Charge::STATUS_ERROR;
			$charge->save();

			return [
				'status' => false,
			];
		}
	}

	public function sepForm($token)
	{

		$token = \Cache::get($token);

		return '
<form action="https://sep.shaparak.ir/payment.aspx" method="post" id="form">
	<input type="hidden" name="Token" value="' . $token . '">
	<input type="hidden" name="RedirectURL" value="https://pos.proshasoft.com/payment/order_verify?gateway=ir_sep"></div>
</form>

<script type="text/javascript">
	document.getElementById(\'form\').submit()
</script>';
	}

	/**
	 * @param Request $request
	 *
	 * @return string
	 */
	public function verify(Request $request)
	{
//		$lastCharge = Charge::where('reference_number', $request->RefNum)
//			->first();
//		if ($lastCharge) {
//			return 'ERROR';
//		}

		$charge = Charge::where('order_id', $request->ResNum)
			->with([
				'order',
			])
			->first();

		if (!$charge) {
			return 'ERROR';
		}

		\Log::info($request->all());

		// request to gateway
		$driver   = Gateway::driver($charge->method);
		$response = $driver->setApiToken($charge->order->company->gateway_token)
			->verify($request->RefNum);

		if ($response) {
			$charge->status           = Charge::STATUS_DONE;
			$charge->reference_number = $request->RefNum;
			$charge->save();

			$order                  = Order::where('id', $charge->order->id)->first();
			$order->payment_confirm = Order::PAYMENT_SUCCESSFUL;
			$order->transfer_number = $request->rrn;
			$order->save();

			return redirect('pos://payment_success_full');
		} else {
			$charge->status           = Charge::STATUS_UNDONE;
			$charge->reference_number = $request->RefNum;
			$charge->save();

			$order                  = Order::where('id', $charge->order->id)->first();
			$order->payment_confirm = Order::PAYMENT_UNSUCCESSFUL;
			$order->save();

			return redirect('pos://payment_failed');
		}

	}
}
