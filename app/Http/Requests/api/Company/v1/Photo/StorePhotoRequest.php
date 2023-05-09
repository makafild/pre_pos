<?php

namespace App\Http\Requests\api\Company\v1\Photo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StorePhotoRequest
 *
 * @package App\Http\Requests\api\Company\v1\Photo
 *
 * @property array $photos
 */
class StorePhotoRequest extends FormRequest
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
			'photos'        => 'required|array|max:20',
			'photos.*.kind' => 'required|in:base64,file',

			'photos.*.file'                => 'required',
			'photos.*.extension'           => 'required|in:jpg,jpeg,png',
			'photos.*.product_referral_id' => [
				'required',
				Rule::exists('products', 'referral_id')
					->where('company_id', auth()->id()),
			],
		];
	}
}
