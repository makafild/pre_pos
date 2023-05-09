<?php

namespace App\Http\Requests\User\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
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
			'name' => 'required',
			//			'permissions' => 'required|array|exists:permissions,id',

			'row_version' => ['required', Rule::exists('roles')->where('id', $this->id),],
		];
	}
}
