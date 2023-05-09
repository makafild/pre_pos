<?php

namespace App\Http\Requests\api\Customer\v1\Order\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class CheckCouponRequest
 *
 * @package App\Http\Requests\api\Customer\v1\Order\Coupon
 * @property string $coupon
 * @property int    $company_id
 */
class CheckCouponRequest extends FormRequest
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
			'coupon'     => 'required',
			'company_id' => [
				'required',
				Rule::exists('users', 'id')
					->where('kind', 'company'),
			],
		];
	}
}
