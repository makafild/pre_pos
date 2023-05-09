<?php

namespace App\Http\Requests\Order\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
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
			'company'        => '',
			'payment_method' => 'required',
			'discount'       => 'required|numeric',
			'discount_max'   => 'required|numeric',
		];
	}
}
