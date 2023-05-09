<?php

namespace App\Http\Requests\api\Company\v1\PriceClass;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceClassRequest extends FormRequest
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
			'price_classes'               => 'required|array',
			'price_classes.*.title'       => 'required',
			'price_classes.*.referral_id' => 'required',
		];
	}
}
