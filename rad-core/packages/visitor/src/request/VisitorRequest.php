<?php

namespace Core\Packages\visitor\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class VisitorRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'point_ids' => 'required|array',
            'point_ids.*' => 'required|integer',
        ];
    }
}
