<?php

namespace App\Http\Requests\Product\Promotions;

use App\Models\Product\Promotions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StorePromotionsRequest
 *
 * @package App\Http\Requests\Product\Promotions
 * @property string $kind
 * @property string $basket_kind
 * @property string $title
 * @property string $description
 * @property int    $discount
 * @property object $product
 * @property object $baskets
 * @property object $awards
 */
class StorePromotionsRequest extends FormRequest
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
			'kind'        => 'required|in:' . implode(',', Promotions::KINDS),
			'basket_kind' => 'required|in:' . implode(',', Promotions::BASKET_KINDS),
			'title'       => 'required',

			'discount'           => 'integer',
			'product.product.id' => [
				Rule::exists('products', 'id'),
			],
			'product.master'     => 'integer',
			'product.slave'      => 'integer',
			'product.slave2'     => 'integer',

			'category.id' => [
				'nullable',
				Rule::exists('categories', 'id'),
			],

			'baskets'      => 'array',
			'baskets.*.id' => [
				Rule::exists('products', 'id'),
			],

			'awards'      => 'array',
			'awards.*.id' => [
				Rule::exists('products', 'id'),
			],
		];
	}
}
