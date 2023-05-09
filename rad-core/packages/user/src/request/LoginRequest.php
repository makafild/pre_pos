<?php

namespace Core\Packages\user\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class LoginRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|max:100',
            'password' => 'required',
        ];
    }
}
