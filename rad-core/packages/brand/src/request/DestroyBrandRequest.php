<?php

namespace Core\Packages\brand\src\request;

use App\Rules\CheckBrandHasProduct;
use App\Rules\CheckRowVersion;
use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;

class DestroyBrandRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'brands' => [
                'required',
                'array',
                new CheckBrandHasProduct(),
            ]
        ];
    }
}
