<?php

namespace Core\Packages\visitor\src\request;

use Core\System\Http\Requests\FormRequestCustomize ;

class VisitorRouteRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'visitor_id' => 'required|array',
            'from' => 'required|date',
            'to' => 'required|date',

        ];
    }
}
