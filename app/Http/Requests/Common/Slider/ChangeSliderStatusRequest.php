<?php

namespace App\Http\Requests\Common\Slider;

use App\Models\Common\Slider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ChangeSliderStatusRequest
 *
 * @package App\Http\Requests\Common\Slider
 * @property object $status
 */
class ChangeSliderStatusRequest extends FormRequest
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
			'status.name' => 'required|in:' . implode(',', Slider::STATUS),
			'row_version' => [
				'required',
				Rule::exists('sliders')
					->where('id', $this->id),
			],
		];
	}
}
