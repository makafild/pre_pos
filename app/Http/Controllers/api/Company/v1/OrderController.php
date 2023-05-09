<?php

namespace App\Http\Controllers\api\Company\v1;

use App\Http\Requests\api\Company\v1\Order\UpdateOrderRequest;
use App\Http\Resources\api\Company\v1\Order\OrderResource;
use App\Models\Order\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */


    public function index()
	{
		$companyId = auth('mobile')->user()->company_id;

		$orders = Order::CompanyId($companyId)
			->with([
				'Customer.Referrals',
				'Details.Product',
			])
			->latest()
			->paginate();

		return OrderResource::collection($orders);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param UpdateOrderRequest $request
	 * @return void
	 */
	public function update(UpdateOrderRequest $request)
	{
		$companyId = auth('mobile')->user()->company_id;

		foreach ($request->orders as $order) {
			Order::CompanyId($companyId)
				->ReferralId($order['referral_id'])
				->update([
					'tracker_url' => $order['tracker_url'],
					'factor_id'   => $order['factor_id'],
					'status'      => $order['status'],
				]);
		}

		return [
			'status'  => true,
			'message' => trans('messages.order.order.update'),
		];
	}
}
