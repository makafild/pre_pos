<?php

namespace Core\Packages\group\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class DestroyRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'id' => 'required',
        ];
    }
}
