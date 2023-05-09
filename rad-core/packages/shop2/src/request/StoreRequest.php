<?php

namespace Core\Packages\shop\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => [
                "nullable", "unique:users"
            ],
            'mobile_number' => [
                Rule::unique('users')
                    ->where('mobile_number', $this->mobile_number)
                    ->where('phone_number', $this->phone_number),
            ],
            'phone_number' => [
                'nullable'
            ],

            // 'customer_group' => ['nullable',  Rule::exists('constants', 'id')
            //         ->where('kind',Constant::CUSTOMER_GROUP)],
            // 'customer_class' => ['nullable',  Rule::exists('constants', 'id')
            //     ->where('kind',Constant::CUSTOMER_CLASS)],
            // 'customer_grade' => ['nullable',  Rule::exists('constants', 'id')
            //         ->where('kind',Constant::CUSTOMER_GRADE)],
            'introduction_source' => ['required',  Rule::exists('constants', 'id')
                ->where('kind',Constant::INTRODUCTION_SOURCE)],

            'payment_method_id' => 'nullable|array',
            'payment_method_id.*' => 'nullable|integer',

            'province' => [
                'required'
            ],
            'first_name' => [
                'required', "string"
            ],
            'last_name' => [
                'required', "string"
            ],
            'referral_id' => [
                'nullable'
            ],
            'price_classes' => 'nullable|array',
            'price_classes.*' => ['required', Rule::exists('price_classes', 'id')],
            'city' => [
                'required'
            ],
            'customer_category' => [
                'required', "array"
            ],
            'addresses' => [
                'required', "array"
            ],
            'area' => [
                'nullable'
            ],
            'route' => [
                'nullable'
            ],
            'addresses.*.address' => 'nullable',
            'addresses.*.postal_code' => 'nullable|digits:10',
            'addresses.*.lat' => 'nullable',
            'addresses.*.long' => 'nullable',
            'password' => ['required', 'string', 'min:1', 'confirmed'],
            'description' =>'nullable'
        ];
    }
}
