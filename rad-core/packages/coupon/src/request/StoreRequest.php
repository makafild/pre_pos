<?php

namespace Core\Packages\coupon\src\request;

use TijsVerkoyen\CssToInlineStyles\Css\Rule\Rule;
use Core\System\Http\Requests\FormRequestCustomize;

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
            'coupon' => [
                "required", "unique:coupons"
            ],

             'coupon_percentage' => 'required',
             'discount_max'=>'required',
            //  'discount_max'=> Rule::requiredIf( function () use ($request){
            //     return $request->coupon_percentage == ;
            // }),
        ];
    }
}
