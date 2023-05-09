<?php

namespace App\Http\Requests\api\Customer\v1\User;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateUserRequest
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
class UpdateUserRequest extends FormRequest
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
			'email'         => 'nullable|unique:users|email',
			'mobile_number' => 'nullable|unique:users',
			'password'      => 'nullable|confirmed',

			'first_name' => 'nullable|string',
			'last_name'  => 'nullable|string',

			'photo_id' => 'nullable|exists:files,id',
		];
	}
}
