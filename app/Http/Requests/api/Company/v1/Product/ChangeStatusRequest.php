<?php

namespace App\Http\Requests\api\Company\v1\Product;

use App\Models\Product\Product;
use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeStatusRequest extends FormRequest
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
			'products.*.referral_id' => [
				'required',
				Rule::exists('products', 'referral_id')
					->where('company_id', auth('mobile')->user()->id),
			],
			'products.*.status'      => [
				'nullable',
				'in:' . implode(',', Product::STATUS),
			],
			'products.*.show_status' => [
				'nullable',
				'in:' . implode(',', User::STATUS),
			],
		];
	}
}
