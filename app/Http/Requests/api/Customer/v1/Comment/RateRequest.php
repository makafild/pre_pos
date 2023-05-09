<?php

namespace App\Http\Requests\api\Customer\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;


class RateRequest extends FormRequest
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
			'action' => 'required|in:like,dislike',
			'value' => 'in:0,1'
		];
	}
}
