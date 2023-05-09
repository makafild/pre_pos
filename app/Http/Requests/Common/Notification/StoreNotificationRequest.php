<?php

namespace App\Http\Requests\Common\Notification;

use App\Models\Setting\Constant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreNotificationRequest
 *
 * @package App\Http\Requests\Common\Notification
 * @property string $title
 * @property string $message
 * @property string $link
 *
 * @property array  $categories
 * @property array  $countries
 * @property array  $provinces
 * @property array  $cities
 */
class StoreNotificationRequest extends FormRequest
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
			'message' => 'required|max:200',
			'link'    => 'nullable|url',

			//			'categories'      => 'array',
			//			'categories.*.id' => [
			//				'required',
			//				Rule::exists('constants', 'id')
			//					->where('kind', Constant::CUSTOMER_CATEGORY),
			//			],
		];
	}
}
