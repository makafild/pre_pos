<?php

namespace Core\Packages\product\src\request;

use App\Rules\CheckBrandHasProduct;
use App\Rules\CheckRowVersion;
use Core\System\Http\Requests\FormRequestCustomize ;

class GetSubProductRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'category_id' => [
                'required',
                "array",
                "exists:categories,id"
            ],
        ];
    }
}
