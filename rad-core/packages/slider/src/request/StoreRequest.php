<?php

namespace Core\Packages\slider\src\request;

use Illuminate\Validation\Rule;
use Core\Packages\slider\Slider;
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

            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'photo_id'     => 'required|exists:files,id',
            'kind' => [
                'required',
                'in:' . implode(',', Slider::KINDS),
            ],
            'company_id'  => [
                'nullable',
                'required_if:kind,' . Slider::KIND_COMPANY,
                'required_if:kind,' . Slider::KIND_PRODUCT,
                Rule::exists('users', 'id')
                    ->where('kind', 'company'),
            ],
            'product_id'  => [
                'nullable',
                'required_if:kind,' . Slider::KIND_PRODUCT,
                Rule::exists('products', 'id')
                    ->where('company_id', $this->company_id),
            ],
            'link' => [
                'required_if:kind,' . Slider::KIND_LINK

            ],
            'provinces' => 'required|array|exists:provinces,id',
            'cities' => 'array',
            'areas' => 'array',
            'route' => 'array',
           'provinces.*' => 'integer|exists:provinces,id',
           'cities.*' => 'integer|exists:cities,id',
            'areas.*' => 'integer|exists:areas,id',
            'route.*' => 'integer|exists:routes,id',

        ];
    }
}
