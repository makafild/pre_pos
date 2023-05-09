<?php

namespace App\Http\Requests\api\Customer\v1\Common\Photo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Class StorePhotoRequest
 *
 * @package App\Http\Requests\Common\Photo
 *
 * @property UploadedFile $photo
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
			'extension' => 'required',
			'photo'     => 'required',
		];
	}
}
