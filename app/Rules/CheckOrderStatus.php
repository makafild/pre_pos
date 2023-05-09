<?php

namespace App\Rules;

use App\Models\Order\Order;
use Illuminate\Contracts\Validation\Rule;

class CheckOrderStatus implements Rule
{
	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param  string $attribute
	 * @param  mixed  $value
	 * @return bool
	 */
	public function passes($attribute, $value)
	{
		if (!is_array($value)) {
			return false;
		}

		$orderIds = array_column($value, 'id');
		/** @var Order[] $orders */
		$ordersStatus = Order::whereIn('id', $orderIds)->get()->pluck('status');

		foreach ($ordersStatus as $orderStatus) {
			if ($orderStatus != $ordersStatus[0]) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		return 'همه‌ی سفارشات باید یک وضعیت داشته باشند.';
	}
}
