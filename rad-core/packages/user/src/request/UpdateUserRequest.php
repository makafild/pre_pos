<?php

namespace Core\Packages\user\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class UpdateUserRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'     => "required|email|unique:users,email,{$this->id},id",
            'mobile_number' => 'required|string|max:11|min:11|regex:/^09[0-9]{9}$/u',
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'group_id' => 'nullable|integer',
            'photo_id' => 'nullable',
        ];
    }
}
