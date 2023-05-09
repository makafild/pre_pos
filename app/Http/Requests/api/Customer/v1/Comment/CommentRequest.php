<?php

namespace App\Http\Requests\api\Customer\v1\Comment;

use Illuminate\Foundation\Http\FormRequest;


class CommentRequest extends FormRequest
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
			'type' => 'required|in:product,company',
			'product_id' => 'nullable|required_if:type,product|exists:products,id',
			'company_id' => 'nullable|required_if:type,company|exists:users,id',
			'text' => 'required'
		];
	}
}
