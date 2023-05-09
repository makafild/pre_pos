<?php

namespace App\Http\Requests\api\Customer\v1\User\CompanyReport;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class TurnoverRequest
 *
 * @package App\Http\Requests\api\Customer\v1\User\CompanyReport
 * @property int[] $companies_id
 */
class StoreCompanyReportRequest extends FormRequest
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
			'companies_id' => [
				'required',
				'array',
				Rule::exists('users', 'id')
					->where('kind', 'company'),
			],
		];
	}
}
