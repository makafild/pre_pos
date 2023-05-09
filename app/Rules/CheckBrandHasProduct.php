<?php

namespace App\Rules;

use Core\Packages\product\Product;
use Illuminate\Contracts\Validation\Rule;

class CheckBrandHasProduct implements Rule
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

		$brandIds = array_column($value, 'id');

		$products = Product::whereIn('brand_id', $brandIds)
			->select('brand_id')
			->groupBy('brand_id')
			->get();

		if (count($products))
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
		return 'برندها دارای محصول هستند.';
	}
}
