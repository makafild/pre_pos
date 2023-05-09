<?php

namespace Core\Packages\group\src\request;

use Core\System\Http\Requests\FormRequestCustomize;
use Core\Packages\group\Group;

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
            'name' => 'min:3',
            'for' => ['in:' . implode(',', Group::FOR_KINDS)],
            'access' => 'array',
        ];
    }
}
