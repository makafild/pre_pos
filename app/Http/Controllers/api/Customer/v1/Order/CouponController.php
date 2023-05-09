<?php

namespace App\Http\Controllers\api\Customer\v1\Order;

use App\Http\Requests\api\Customer\v1\Order\Coupon\CheckCouponRequest;
use App\Models\Order\Coupon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CouponController extends Controller
{
	public function check(CheckCouponRequest $request)
	{
		/** @var Coupon $coupon */
		$coupon = Coupon::where([
			'coupon'     => $request->coupon,
			'company_id' => $request->company_id,
		])->with([
			'CouponCustomer' => function ($query) {
				$query->where('user_id', '=', auth()->id());
			},
		])->active()->first();

		if ($coupon) {
			if (count($coupon->CouponCustomer))
				return [
					'status'  => false,
					'message' => 'شما از این کد تخفیف یکبار استفاده کردید.',
				];

			return [
				'status'     => true,
				'percentage' => $coupon->percentage,
				'message'    => trans('messages.api.customer.order.coupon.check', ['percentage' => $coupon->percentage]),
			];
		}

		return [
			'status'  => false,
			'message' => trans('messages.api.customer.order.coupon.not_check'),
		];
	}
}
