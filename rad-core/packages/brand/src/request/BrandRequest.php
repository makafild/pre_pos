<?php

namespace Core\Packages\brand\src\request;

use Illuminate\Validation\Rule;
use Core\System\Http\Requests\FormRequestCustomize ;

class BrandRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_fa'   => ['required','unique:brands,name_fa,'.$this->brand],
            'name_en'  => ['required','nullable','unique:brands,name_en,'.$this->brand
        ],
            'photo_id' => 'required|nullable|exists:files,id',
        ];
    }
}
