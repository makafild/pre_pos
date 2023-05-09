<?php

namespace App\Http\Requests\User\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
			'email'         => "required|email|unique:users,email,{$this->id},id",
			'mobile_number' => "required|unique:users,mobile_number,{$this->id},id",
			'password'      => 'confirmed',

			'first_name' => 'required',
			'last_name'  => 'required',

			'photo_id' => 'nullable|exists:files,id',

			'role.id' => 'required|exists:roles,id',

			'row_version' => ['required', Rule::exists('users')->where('id', $this->id),],
		];
	}
}
