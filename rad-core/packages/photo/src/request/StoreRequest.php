<?php

namespace Core\Packages\photo\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

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
            'photo' => 'required|file|mimes:jpeg,bmp,png,gif|max:100',
        ];
    }
}
