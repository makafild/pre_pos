<?php

namespace App\Http\Requests\Product\Product;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeProductStatusRequest extends FormRequest
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
		$rules = [];

		$rules['products'] = [
			'required',
			'array',
			new CheckRowVersion('products'),
		];
		$rules['products.*.id'] = [
			'required',
		];
		$rules['products.*.row_version'] = [
			'required',
		];
		$rules['status.name'] = 'required|in:available,unavailable';

		return $rules;
	}
}
