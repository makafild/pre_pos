<?php

namespace App\Http\Requests\Common\Slider;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Class DestroySliderRequest
 *
 * @package App\Http\Requests\Common\Slider
 * @property array $sliders
 */
class DestroySliderRequest extends FormRequest
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
			'sliders' => [
				'required',
				'array',
				new CheckRowVersion('sliders'),
			],
		];
	}
}
