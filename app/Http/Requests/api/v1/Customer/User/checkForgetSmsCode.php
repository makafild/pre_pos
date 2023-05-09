<?php

namespace App\Http\Requests\api\v1\Customer\User;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class checkForgetSmsCode
 *
 * @package App\Http\Requests\api\v1\Customer\User
 * @property string $mobile_number
 * @property string $code
 * @property string $password
 */
class checkForgetSmsCode extends FormRequest
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
			'mobile_number' => [
				'required',
				Rule::exists('users', 'mobile_number')
					->where('kind', User::KIND_CUSTOMER),
			],
			'code'          => [
				'required',
				Rule::exists('sms_validations', 'code')
					->where('mobile_number', $this->mobile_number),
			],
			'password'      => 'required|confirmed',
		];
	}
}
