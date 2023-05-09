<?php

namespace Core\Packages\Role\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class PermissionRoleRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'permissions' => 'required|array',
            'permissions.*' => 'required|string'
        ];
    }
}
