<?php

namespace App\Http\Requests\api\Customer\v1\User;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;
use App\Http\Helpers;

/**
 * Class UserRequest
 *
 * @package App\Http\Requests\api\Customer\v1\User
 * @property string email
 * @property string password
 */
class LoginRequest extends FormRequest
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
			'email'    => [
				'required',
//				Rule::exists('users', 'mobile_number')
//					->where('kind', User::KIND_CUSTOMER),
			],
			'password' => 'required',
		];
	}

	public function all($keys = NULL)
	{

		$results = parent::all($keys);

		$results['email'] = Helpers::numberToEnglish($results['email']);
		$results['password'] = Helpers::numberToEnglish($results['password']);

		return $results;
	}

	public function messages()
	{
		return [
			'email.exists' => 'کاربری با این شماره تلفن یافت نشد.'
		];
	}
}
