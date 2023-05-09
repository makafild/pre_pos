<?php

namespace App\Rules;

use App\Models\Order\Order;
use Illuminate\Contracts\Validation\Rule;

class CheckOrderReferralIds implements Rule
{
	private $orderId;
	private $customerFlag;
	private $productFlag;
	private $status;

	/**
	 * Create a new rule instance.
	 *
	 * @return void
	 */
	public function __construct($status)
	{
		//
		$this->status = $status;
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
		if ($this->status == Order::STATUS_REJECTED) {
			return true;
		}

		/** @var Order $order */
		$order = Order::with([
			'customer',
			'details.product',
		])->where('id', $value)->first();

		$this->orderId = $order->id;

		if (!$order->customer->getReferralIdBy($order->company_id)) {
			$this->customerFlag = true;
		}

		foreach ($order->details as $detail) {
			if (!$detail->product->referral_id) {
				$this->productFlag = true;
			}
		}

		if ($this->customerFlag || $this->productFlag)
			return false;

		return true;
	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message()
	{
		$message = "سفارش {$this->orderId}: ";
		if ($this->customerFlag)
			$message .= "کد مرجع مشتری ندارد.";
		if ($this->productFlag)
			$message .= "کد مرجع محصولات ندارد.";

		return $message;
	}
}
