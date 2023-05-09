<?php

namespace App\Http\Requests\Company\IntroducerCode;

use App\Rules\CheckRowVersion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeIntroducerCodeStatusRequest extends FormRequest
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
		$rules = [];

		$rules['introducer_codes'] = [
			'required',
			'array',
		];
		$rules['introducer_codes.*.id'] = [
			'required',
		];
		$rules['status.name'] = 'required|in:active,inactive';

		return $rules;
	}
}
