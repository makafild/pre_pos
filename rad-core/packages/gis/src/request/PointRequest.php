<?php

namespace Core\Packages\Gis\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class PointRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'route_id' => 'required|string',
            'user_id' => 'required|string',
            'lan' => 'required|string',
            'lat' => 'required|string',
            'state' => 'required|string',
        ];
    }
}
