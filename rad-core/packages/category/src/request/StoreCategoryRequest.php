<?php

namespace Core\Packages\category\src\request;

use App\Rules\CheckBrandHasProduct;
use App\Rules\CheckRowVersion;
use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;

class StoreCategoryRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title'     => 'required',
            'parent_id' => 'required|exists:categories,id',
        ];
    }
}
