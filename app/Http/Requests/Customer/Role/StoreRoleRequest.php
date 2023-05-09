<?php

namespace App\Http\Requests\Customer\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreRoleRequest
 *
 * @package App\Http\Requests\User\Role
 * @property string $name
 */
class StoreRoleRequest extends FormRequest
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
			'name' => [
				'required',
				Rule::unique('roles')
					->where('kind', 'customer_api')
					->where('company_id', auth()->id()),
			]
			//			'permissions' => 'required|array|exists:permissions,id',
		];
	}
}
