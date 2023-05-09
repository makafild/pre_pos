<?php

namespace App\Http\Requests\Setting\Setting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateSettingRequest
 *
 * @package App\Http\Requests\Setting\Setting
 * @property string $value
 */
class UpdateSettingRequest extends FormRequest
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
			'value' => 'required',
		];
	}
}
