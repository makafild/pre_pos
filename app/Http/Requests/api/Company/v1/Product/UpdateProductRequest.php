<?php

namespace App\Http\Requests\api\Company\v1\Product;

use App\Models\Setting\Constant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest
 *
 * @package App\Http\Requests\api\Company\v1\Product
 * @property string $referral_id
 *
 * @property string $name_fa
 * @property string $name_en
 * @property string $description
 *
 * @property int    $per_master
 * @property int    $per_slave
 *
 * @property array  $master_unit_id
 * @property array  $slave_unit_id
 * @property array  $slave2_unit_id
 *
 * @property int    $sales_price
 * @property int    $consumer_price
 * @property int    $discount
 *
 * @property array  $brand_id
 * @property array  $category_id
 * @property int    $photo_id
 * @property int    $company_id
 *
 * @property array  $sale_prices
 *
 */
class UpdateProductRequest extends FormRequest
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
			'products'               => 'required|array',
			'products.*.referral_id' => [
				'required',
				Rule::exists('products', 'referral_id')
					->where('company_id', auth('mobile')->user()->id),
			],
			'products.*.name_fa'     => 'required',
			'products.*.name_en'     => 'required',
			'products.*.description' => '',

			'products.*.per_master' => 'required|integer',
			'products.*.per_slave'  => 'required|integer',

			'products.*.master_unit_id' => 'required|exists:constants,id,kind,' . Constant::UNIT,
			'products.*.slave_unit_id'  => 'required|exists:constants,id,kind,' . Constant::UNIT,
			'products.*.slave2_unit_id' => 'exists:constants,id,kind,' . Constant::UNIT,

			'products.*.purchase_price' => 'required|integer',
			'products.*.sales_price'    => 'required|integer',
			'products.*.consumer_price' => 'required|integer',
			'products.*.discount'       => '',

			'products.*.brand_id'    => 'required|exists:brands,id',
			'products.*.category_id' => 'required|exists:categories,id',
			'products.*.photo_id'    => 'nullable|exists:files,id',

			'products.*.sale_prices.*.price'                => 'required',
			'products.*.sale_prices.*.customer_category.id' => 'required',
		];
	}
}
