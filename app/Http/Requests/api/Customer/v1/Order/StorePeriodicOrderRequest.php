<?php

namespace App\Http\Requests\api\Customer\v1\Order;

use App\Models\Product\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreOrderRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Order
 * @property string
 * @property array $products
 * @property array $days
 */
class StorePeriodicOrderRequest extends FormRequest
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
			'products'          => 'required|array',
			'products.*.id'     => [
				'required',
				Rule::exists('products', 'id')->where(function ($query) {
					return $query->where('status', Product::STATUS_AVAILABLE);
				}),
			],
			'products.*.master' => 'required|integer|min:0',
			'products.*.slave'  => 'required|integer|min:0',
			'products.*.slave2' => 'required|integer|min:0',
			'days'              => 'required|integer|min:1',
		];
	}
}
