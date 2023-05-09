<?php

namespace App\Http\Requests\api\Company\v1\Customer;

use App\Rules\isMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
			'customers'                 => 'required|array',
			'customers.*.referral_id'   => [
				'required',
				Rule::unique('company_customers', 'referral_id')
					->where('company_id', auth('mobile')->user()->id),
			],
			'customers.*.first_name'    => 'nullable|string',
			'customers.*.last_name'     => 'nullable|string',
			'customers.*.national_id'   => 'nullable|string',
			'customers.*.mobile_number' => [
				'nullable',
				Rule::unique('company_customers', 'mobile_number')
					->where('company_id', auth()->id()),
				new isMobile(),
			],
			'customers.*.email'         => [
				'nullable',
				'email',
				Rule::unique('company_customers', 'email')
					->where('company_id', auth()->id()),
			],

			'customers.*.address' => 'nullable',
			'customers.*.lat'     => 'nullable',
			'customers.*.lng'     => 'nullable',
		];
	}
}
