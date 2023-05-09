<?php

namespace Core\Packages\company\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;

class StoreRequest extends FormRequestCustomize
{
    public function rules()
    {
        return [
            'email'         => 'required|unique:users|email',
            'mobile_number' => 'required|unique:users|regex:/^09[0-9]{9}$/u',
            'password'      => 'required|confirmed',

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
            'countries.*.id' => 'required|exists:countries,id',

            'provinces'      => 'required|array',
            'provinces.*.id' => 'required|exists:provinces,id',

            'cities'      => 'required|array',
            'cities.*.id' => 'required|exists:cities,id',

            'brands'      => 'required|array',
            'brands.*.id' => 'required|exists:brands,id',

            'addresses' => 'array',
            //			'addresses.*.address'     => 'required',
            //			'addresses.*.postal_code' => 'required',
            //			'addresses.*.lat'         => 'required',
            //			'addresses.*.long'        => 'required',

            'contacts' => 'array',
            'show_users_list' => 'nullable|in:0,1',
            //			'contacts.*.kind'  => 'required',
            //			'contacts.*.value' => 'required',
        ];
    }
}
