<?php

namespace App\Http\Requests\Order\Coupon;

use App\Rules\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreCouponRequest
 *
 * @package App\Http\Requests\Order\Coupon
 *
 * @property string $coupon
 * @property int    $percentage
 * @property int    $discount_max
 *
 * @property string $start_at
 * @property string $end_at
 */
class StoreCouponRequest extends FormRequest
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
			'coupon'       => [
				'required',
				Rule::unique('coupons', 'coupon')
					->where('company_id', auth('mobile')->user()->company_id),
			],
			'percentage'   => 'required|between:0,99.99|min:0|max:100',
			'discount_max' => 'integer',

			'start_at' => [
				'required',
				new Jalali(),
			],
			'end_at'   => [
				'required',
				new Jalali(),
			],
		];
	}
}
