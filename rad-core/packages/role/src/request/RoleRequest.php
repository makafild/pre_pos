<?php

namespace Core\Packages\Role\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'permissions' => 'required|array',
            'permissions.*' => 'required|numeric'
        ];
    }
}
