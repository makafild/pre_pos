<?php

namespace App\Http\Requests\User\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class ChangePasswordRequest
 *
 * @package App\Http\Requests\User\User
 * @property string $password
 */
class ChangePasswordRequest extends FormRequest
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
			'password' => 'required|confirmed',
		];
	}
}
