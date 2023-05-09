<?php

namespace App\Http\Requests\Common\Photo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * Class StorePhotoRequest
 *
 * @package App\Http\Requests\Common\Photo
 *
 * @property UploadedFile $photo
 */
class StoreFileRequest extends FormRequest
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
			'photo' => 'required|file',
		];
	}
}
