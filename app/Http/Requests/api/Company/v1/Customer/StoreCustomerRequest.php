<?php

namespace App\Http\Requests\api\Company\v1\Customer;

use App\Rules\isMobile;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
			],
			'customers.*.first_name'    => 'nullable|string',
			'customers.*.last_name'     => 'nullable|string',
			'customers.*.national_id'   => 'nullable|string',
			'customers.*.mobile_number' => [
				'nullable',
			],
			'customers.*.email'         => [
				'nullable',
				'email',
			],

			'customers.*.address'       => 'nullable',
			'customers.*.lat'           => 'nullable',
			'customers.*.lng'           => 'nullable',
		];
	}
}
