<?php

namespace App\Http\Requests\Common\Survey;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroySurveyRequest
 *
 * @package App\Http\Requests\Common\Survey
 * @property array $surveys
 */
class DestroySurveyRequest extends FormRequest
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
			'surveys' => [
				'required',
				'array',
				new CheckRowVersion('surveys'),
			],
		];
	}
}
