<?php

namespace Core\Packages\user\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class UserRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|string|unique:users|email|max:100',
            'password' => 'required|string|confirmed|min:6',
            'mobile_number' => 'required|string|max:11|min:11|regex:/^09[0-9]{9}$/u',
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'group_id' => 'nullable|integer',
            'photo_id' => 'nullable',
        ];
    }
}
