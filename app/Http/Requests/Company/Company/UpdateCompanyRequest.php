<?php

namespace App\Http\Requests\Company\Company;

use App\Rules\Jalali;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Class StoreCompanyRequest
 *
 * @package App\Http\Requests\Company\Company
 *
 * @property string $email
 * @property string $mobile_number
 * @property string $password
 *
 * @property string $name_fa
 * @property string $name_en
 * @property string $economic_code
 * @property string $api_url
 * @property string $gateway_token
 * @property string $lat
 * @property string $long
 *
 * @property string $end_at
 *
 * @property int    $photo_id
 *
 * @property int[]  $countries
 * @property int[]  $provinces
 * @property int[]  $cities
 * @property int[]  $brands
 *
 * @property array  $addresses
 * @property array  $contacts
 */
class UpdateCompanyRequest extends FormRequest
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
			'email'         => [
				'required',
				Rule::unique('users')->ignore($this->id)
			],
			'mobile_number' => [
				'required',
				Rule::unique('users')->ignore($this->id)
			],
			'password'      => 'nullable|confirmed',

			'name_fa'       => 'required',
			'name_en'       => 'nullable',
			'economic_code' => 'nullable',
			'api_url'       => 'nullable|url',
			'gateway_token' => 'nullable',
			'lat'           => 'nullable',
			'long'          => 'nullable',

			'end_at' => [
				'required',
				new Jalali(),
			],

			'photo_id' => 'nullable|exists:files,id',

			'countries'      => 'required|array',
			'countries.*.id' => 'required|exists:countries,id',

			'provinces'      => 'required|array',
			'provinces.*.id' => 'required|exists:provinces,id',

			'cities'      => 'required|array',
			'cities.*.id' => 'required|exists:cities,id',

			'brands'      => 'required|array',
			'brands.*.id' => 'required|exists:brands,id',

			'addresses' => 'array',

			'addresses.*.id'      => [
				Rule::exists('addresses', 'id')
					->where('user_id', $this->id),
			],
			//			'addresses.*.address'     => 'required',
			//			'addresses.*.postal_code' => 'required',
			//			'addresses.*.lat'         => 'required',
			//			'addresses.*.long'        => 'required',

			'contacts' => 'array',
			//			'contacts.*.kind'  => 'required',
			//			'contacts.*.value' => 'required',

			'row_version' => ['required', Rule::exists('users')->where('id', $this->id),],
		];
	}
}
