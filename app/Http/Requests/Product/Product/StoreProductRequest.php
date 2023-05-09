<?php

namespace App\Http\Requests\Product\Product;

use App\Models\Setting\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreProductRequest
 *
 * @package App\Http\Requests\Product\Product
 *
 * @property string $name_fa
 * @property string $name_en
 * @property string $description
 *
 * @property int    $per_master
 * @property int    $per_slave
 *
 * @property int    $quotas_master
 * @property int    $quotas_slave
 * @property int    $quotas_slave2
 *
 * @property int    $master_status
 * @property int    $slave_status
 * @property int    $slave2_status
 *
 * @property array  $master_unit
 * @property array  $slave_unit
 * @property array  $slave2_unit
 *
 * @property int    $sales_price
 * @property int    $consumer_price
 * @property int    $discount
 *
 * @property array  $brand
 * @property array  $category
 * @property int    $photo_id
 * @property int    $company_id
 *
 * @property array  $sale_prices
 *
 * @property string $referral_id
 */
class StoreProductRequest extends FormRequest
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
			'name_fa' => 'required',

			'per_master' => 'required|integer',
			'per_slave'  => 'required|integer',

			'quotas_master' => 'required|integer',
			'quotas_slave'  => 'required|integer',
			'quotas_slave2' => 'required|integer',

			'master_status' => 'required|integer|in:0,1',
			'slave_status'  => 'required|integer|in:0,1',
			'slave2_status' => 'required|integer|in:0,1',

			'master_unit.id' => 'exists:constants,id,kind,' . Constant::UNIT,
			'slave_unit.id'  => 'exists:constants,id,kind,' . Constant::UNIT,
			'slave2_unit.id' => 'required|exists:constants,id,kind,' . Constant::UNIT,

			'purchase_price' => 'required|integer',
			'sales_price'    => 'required|integer',
			'consumer_price' => 'required|integer',

			'brand.id'    => 'required|exists:brands,id',
			'category.id' => 'required|exists:categories,id',
			'photo_id'    => 'nullable|exists:files,id',

			'price_classes.*.pivot.price'    => 'required',
			'price_classes.*.price_class.id' => 'required',
			'photos'               => 'array',
			'photos.*.id'          => 'required|exists:files,id',
			'user_categories'      => 'array',
			'user_categories.*.id' => 'required|exists:constants,id,kind,' . Constant::CUSTOMER_CATEGORY,

			'barcodes'           => 'array',
			'barcodes.*.barcode' => 'required',
		];
	}
}
