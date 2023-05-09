<?php

namespace App\Http\Requests\Setting\Brand;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreBrandRequest
 *
 * @package App\Http\Requests\Setting\Brand
 * @property string $name_en
 * @property string $name_fa
 * @property int    $photo_id
 */
class StoreBrandRequest extends FormRequest
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
			'name_fa'  => 'required',
			'name_en'  => 'nullable',
			'photo_id' => 'nullable|exists:files,id',
		];
	}
}
