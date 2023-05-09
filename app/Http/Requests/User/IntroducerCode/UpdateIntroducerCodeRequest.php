<?php

namespace App\Http\Requests\User\IntroducerCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateIntroducerCodeRequest
 *
 * @package App\Http\Requests\User\IntroducerCode
 * @property string $title
 * @property string $code
 */
class UpdateIntroducerCodeRequest extends FormRequest
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
			'title' => 'required',
			'code'  => [
				'required',
				Rule::unique('introducer_codes')
					->ignore($this->id),
			],
		];
	}
}
