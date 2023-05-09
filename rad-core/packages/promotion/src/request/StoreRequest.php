<?php

namespace Core\Packages\promotion\src\request;

use Core\Packages\promotion\Promotions;
use Core\Packages\user\Users;
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
            'kind' => 'required|in:' . implode(',', Promotions::KINDS),
            'basket_kind' => 'required|in:' . implode(',', Promotions::BASKET_KINDS),
            'title' => 'required',
            'description' => 'required',

            'discount' => 'integer',
            'product.product.id' => [
                Rule::exists('products', 'id'),
            ],
            'product.master' => 'integer',
            'product.slave' => 'integer',
            'product.slave2' => 'integer',

            'category.*.id' => [
                'nullable',
                Rule::exists('categories', 'id'),
            ],
            'brands' => 'array',
            'brands.*.id' => [
                'nullable',
                Rule::exists('brands', 'id'),
            ],

            'baskets' => 'array',
            'baskets.*.id' => [
                Rule::exists('products', 'id'),
            ],

            'awards' => 'array',
            'awards.*.id' => [
                Rule::exists('products', 'id'),
            ],
            'from_date' => 'nullable|required_with:to_date',
            'to_date' => 'nullable|required_with:from_date',
            'provinces' => 'nullable|array|required_with:city_id',
            'provinces.*' => 'required|exists:provinces,id',
            'cities' => 'nullable|array|required_with:route_id',
            'cities.*' => 'required|required_with:city_id|exists:cities,id',
            'areas' => 'nullable|array|required_with:area_id',
            'areas.*' => 'required|required_with:route_id|exists:areas,id',
            'routes' => 'nullable|array',
            'routes.*' => 'required|exists:routes,id',
            'customers' => 'nullable|array',
            'customers.*' => 'nullable|' .
                Rule::exists('users', 'id')
                    ->where('kind', Users::KIND_CUSTOMER),
            'price_classes' => 'nullable|array',
            'price_classes.*' => 'nullable|' .Rule::exists('price_classes', 'id')
        ];
    }
}
