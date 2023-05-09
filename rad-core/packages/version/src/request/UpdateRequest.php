<?php

namespace Core\Packages\version\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class UpdateRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'description' => 'required',
        ];
    }
}
