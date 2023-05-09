<?php

namespace App\Http\Requests\User\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreUserRequest
 *
 * @package App\Http\Requests\user\user
 *
 * @property string $email
 * @property string $mobile_number
 * @property string $password
 *
 * @property string $first_name
 * @property string $last_name
 *
 * @property int    $photo_id
 */
class StoreUserRequest extends FormRequest
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
			'email'         => 'required|unique:users|email',
			'mobile_number' => 'required|unique:users',
			'password'      => 'required|confirmed',

			'first_name' => 'required',
			'last_name'  => 'required',

			'photo_id' => 'nullable|exists:files,id',

			'role.id' => 'required|exists:roles,id',
		];
	}
}
