<?php

namespace App\Http\Requests\api\Company\v1\Order;

use App\Models\Order\Order;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateOrderRequest
 *
 * @package App\Http\Requests\api\Company\v1\Order
 * @property array $orders
 */
class UpdateOrderRequest extends FormRequest
{
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			'orders'               => 'required|array',
			'orders.*.referral_id' => 'required',
			'orders.*.tracker_url' => 'url',
			'orders.*.factor_id'   => '',
			'orders.*.status'      => 'in:' . implode(',', Order::STATUS),
		];
	}
}
