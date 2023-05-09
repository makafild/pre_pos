<?php

namespace Core\Packages\brand\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;
use Core\System\Http\Traits\HelperRequestTrait;

class StoreBrandRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_fa'  => 'required',
            'name_en'  => 'required|nullable',
            'photo_id' => 'required|nullable|exists:files,id',
        ];
    }
}
