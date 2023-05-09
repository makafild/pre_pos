<?php

namespace Core\Packages\tour_visit\src\request;

use Core\System\Http\Requests\FormRequestCustomize;

class TourVisitorsUpdateRequest extends FormRequestCustomize
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'visitor_id'        => 'required|numeric|exists:users,id',
            'route_id'        => 'numeric|required|exists:users,id',
            'dates' => 'required|array',
            'dates.*' => 'required|numeric'
        ];
    }
}
