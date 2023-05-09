<?php

namespace App\Http\Requests\Common\Message;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
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
			'to_id'   => 'required|integer|exists:users,id',
			'message' => 'required|string|min:1|max:255',
		];
	}
}
