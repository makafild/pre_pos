<?php

namespace Core\Packages\shop\src\request;

use Core\Packages\common\Constant;
use Core\System\Http\Requests\FormRequestCustomize ;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequestCustomize
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
               'nullable', 'unique:users,email,'.$this->customer,
            ],
            'mobile_number' => [
                'required','unique:users,mobile_number,'.$this->customer,
            ],
            'phone_number' => [
                'nullable'
            ],
            'price_classes' => 'nullable|array',
            'price_classes.*' => ['required', Rule::exists('price_classes', 'id')],
            // 'customer_group' => ['nullable',Rule::requiredIf( function () {
            //     if(auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') return false;
            //     else return true;
            // }),  Rule::exists('constants', 'id')
            //     ->where('kind',Constant::CUSTOMER_GROUP)],
            // 'customer_class' => ['nullable',Rule::requiredIf( function () {
            //     if(auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') return false;
            //     else return true;
            // }),  Rule::exists('constants', 'id')
            //     ->where('kind',Constant::CUSTOMER_CLASS)],
            // 'customer_grade' => ['nullable',  Rule::exists('constants', 'id')
            //     ->where('kind',Constant::CUSTOMER_GRADE)],
            'introduction_source' => ['nullable',Rule::requiredIf( function () {
                if(auth('api')->user()['kind'] == 'admin' || auth('api')->user()['kind'] == 'superAdmin') return false;
                else return true;
            }),  Rule::exists('constants', 'id')
                ->where('kind',Constant::INTRODUCTION_SOURCE)],

            'province' => [
                'required'
            ],
            'city' => [
                'required'
            ],
            'customer_category' => [
                'required',"array"
            ],
            'area' => [
                'nullable'
            ],
            'route' => [
                'nullable'
            ],
            'addresses' => [
                'nullable',"array"
            ],
            'description' =>'nullable'

        ];
    }
}
