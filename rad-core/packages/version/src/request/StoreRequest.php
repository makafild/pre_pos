<?php

namespace Core\Packages\version\src\request;

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
            'version' => [
                "required", "unique:versions_info"
            ],
            'description' => 'required',
        ];
    }
}
