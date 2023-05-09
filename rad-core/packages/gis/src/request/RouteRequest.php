<?php

namespace Core\Packages\Gis\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class RouteRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'province_id' => 'required',
            'city_id' => 'required',
            'area_id' => 'required',
            'route' => 'required|string',
            "visitors"=>"array|exists:visitors,id",
        ];
    }
}
