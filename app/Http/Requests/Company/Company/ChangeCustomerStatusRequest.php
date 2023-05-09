<?php

namespace App\Http\Requests\Company\Company;

use App\Models\User\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ChangeCustomerStatusRequest
 *
 * @package App\Http\Requests\Comany\Company
 * @property object $status
 */
class ChangeCustomerStatusRequest extends FormRequest
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
			'status.name' => 'required|in:' . implode(',', User::STATUS),
			'row_version' => [
				'required',
				Rule::exists('users')
					->where('id', $this->id),
			],
		];
	}
}
