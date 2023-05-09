<?php

namespace Core\Packages\Role\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class UserRoleRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'role_ids' => 'required|array',
            'role_ids.*' => 'required|integer',
        ];
    }
}
