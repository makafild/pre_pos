<?php

namespace Core\Packages\company\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCompanyRequest extends FormRequestCustomize
{

    public function rules()
    {
        return [
            'email'         => 'required|unique:users|email',
            'mobile_number' => 'required|unique:users|regex:/^09[0-9]{9}$/u',
            'manager_mobile_number' => [
                'nullable',
                'regex:/^09[0-9]{9}$/u'
            ],
            'password'      => 'required|confirmed',
            'role_id'       => 'nullable|exists:roles,id',
            'name_fa'       => 'required',
            'name_en'       => 'nullable',
            'economic_code' => 'nullable',
            'api_url'       => 'nullable|url',
            'gateway_token' => 'nullable',
            'lat'           => 'nullable',
            'long'          => 'nullable',

            'end_at' => [
                'required',
            ],

            'photo_id' => 'nullable|exists:files,id',

            'countries'      => 'required|array',

            'provinces'      => 'required|array',

            'cities'      => 'required|array',

            'brands'      => 'required|array',

            'addresses' => 'array',
            //			'addresses.*.address'     => 'required',
            			'addresses.*.postal_code' => 'digits:10',
            //			'addresses.*.lat'         => 'required',
            //			'addresses.*.long'        => 'required',

            'contacts' => 'array',
            //			'contacts.*.kind'  => 'required',
            //			'contacts.*.value' => 'required',
        ];
    }
}
